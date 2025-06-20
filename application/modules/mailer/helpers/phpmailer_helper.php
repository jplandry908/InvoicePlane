<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * InvoicePlane
 *
 * @author      InvoicePlane Developers & Contributors
 * @copyright   Copyright (c) 2012 - 2018 InvoicePlane.com
 * @license     https://invoiceplane.com/license.txt
 * @link        https://invoiceplane.com
 */
/**
 * @param $from
 * @param $to
 * @param $subject
 * @param $message
 *
 * @return bool
 */
function phpmail_send(
    $from,
    $to,
    $subject,
    $message,
    $attachment_path = null,
    $cc = null,
    $bcc = null,
    $more_attachments = null
) {
    $CI = &get_instance();
    $CI->load->library('crypt');

    // Create the basic mailer object
    $mail          = new \PHPMailer\PHPMailer\PHPMailer();
    $mail->CharSet = 'UTF-8';
    $mail->isHTML();

    // Set msg from PHPMailer in user lang. Only work with 2 letters. See phpmailer.lang-fr.php (in vendor dir).
    $mail->setLanguage(trans('cldr')); // Default ($langcode = 'en', $lang_path = '')

    switch (get_setting('email_send_method')) {
        case 'smtp':
            $mail->isSMTP();
            $mail->SMTPDebug   = env_bool('ENABLE_DEBUG') ? 2 : 0;
            $mail->Debugoutput = env_bool('ENABLE_DEBUG') ? 'echo' : 'error_log';

            // Set the basic properties
            $mail->Host = get_setting('smtp_server_address');
            $mail->Port = get_setting('smtp_port');

            // Is SMTP authentication required?
            if (get_setting('smtp_authentication')) {
                $mail->SMTPAuth = true;

                $decoded = $CI->crypt->decode($CI->mdl_settings->get('smtp_password'));

                $mail->Username = get_setting('smtp_username');
                $mail->Password = $decoded;
            }

            // Is a security method required?
            if (get_setting('smtp_security')) {
                $mail->SMTPSecure = get_setting('smtp_security');
            }

            // Check if certificates should not be verified
            if ( ! get_setting('smtp_verify_certs', true)) {
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ],
                ];
            }

            break;
        case 'sendmail':
        case 'phpmail':
        case 'default':
            $mail->IsMail();
            break;
    }

    $mail->Subject = $subject;
    $mail->Body    = $message;
    $mail->AltBody = $mail->normalizeBreaks($mail->html2text($message));

    if (is_array($from)) {
        // This array should be address, name
        $mail->setFrom($from[0], $from[1]);
    } else {
        // This is just an address
        $mail->setFrom($from);
    }

    // Allow multiple recipients delimited by comma or semicolon
    $to = (mb_strpos($to, ',')) ? explode(',', $to) : explode(';', $to);

    // Add the addresses
    foreach ($to as $address) {
        $mail->addAddress($address);
    }

    if ($cc) {
        // Allow multiple CC's delimited by comma or semicolon
        $cc = (mb_strpos($cc, ',')) ? explode(',', $cc) : explode(';', $cc);

        // Add the CC's
        foreach ($cc as $address) {
            $mail->addCC($address);
        }
    }

    if ($bcc) {
        // Allow multiple BCC's delimited by comma or semicolon
        $bcc = (mb_strpos($bcc, ',')) ? explode(',', $bcc) : explode(';', $bcc);
        // Add the BCC's
        foreach ($bcc as $address) {
            $mail->addBCC($address);
        }
    }

    if (get_setting('bcc_mails_to_admin') == 1) {
        // Get email address of admin account and push it to the array
        $CI->load->model('users/mdl_users');
        $CI->db->where('user_id', 1);
        $admin = $CI->db->get('ip_users')->row();
        $mail->addBCC($admin->user_email);
    }

    $xml_del = false;
    // Add the attachments if needed
    if ($attachment_path && get_setting('email_pdf_attachment')) {
        $mail->addAttachment($attachment_path);

        // eInvoicing replace ARCHIVE (pdf) to TEMP (xml) for no embed_xml - since 1.6.3
        $attachment_path = strtr($attachment_path, [UPLOADS_ARCHIVE_FOLDER => UPLOADS_TEMP_FOLDER]);

        // The XML eInvoicing file exist in temporary?
        $xml_file = mb_rtrim($attachment_path, '.pdf') . '.xml';
        if (file_exists($xml_file)) {
            // Attach eInvoicing temp file
            if ( ! empty($_SERVER['CIIname'])) {
                // Need Specific eInvoice filename? (see mailer helper)
                $mail->addAttachment($xml_file, $_SERVER['CIIname']); // phpmailer-sent-attachment-as-other-name
            } else {
                $mail->addAttachment($xml_file);
            }

            // Need Delete
            $xml_del = true;
        }
    }

    // Add the other attachments if supplied
    if ($more_attachments) {
        foreach ($more_attachments as $paths) {
            $mail->addAttachment($paths['path'], $paths['filename']);
        }
    }

    // And away it goes...
    $ok = $mail->send();

    // Delete the tmp CII-XML file
    if ($xml_del) {
        unlink($xml_file);
    }

    // Only Notify the error. The success is in mailer controller.
    if ( ! $ok) {
        $CI->session->set_flashdata('alert_error', $mail->ErrorInfo);
    }

    return $ok;
}
