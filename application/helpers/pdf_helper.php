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
 *
 * eInvoicing add-ons by Verony
 */

/**
 * Print a html for global discounts table template.
 *
 * @param obj    $obj                 ($invoice or $quote object)
 * @param bool   $show_item_discounts
 * @param string $is                  'invoice' or 'quote'
 *
 * @scope views/[invoice|quote]_templates/pdf/InvoicePlane[| - paid| - overdue].pdf
 */
function discount_global_print_in_pdf($obj, $show_item_discounts, string $is = 'invoice'): void
{
    $type     = ['p' => $is . '_discount_percent', 'a' => $is . '_discount_amount'];
    $discount = 0;
    if ($obj->{$type['p']} != '0.00') { // discount_percent
        $discount = format_amount($obj->{$type['p']}) . '%';
    } elseif ($obj->{$type['a']} != '0.00') { // discount_amount
        $discount = format_currency($obj->{$type['a']});
    }

    if ($discount) {
?>
            <tr>
                <td class="text-right" colspan="<?php echo $show_item_discounts ? '5' : '4'; ?>"><?php
                    echo mb_rtrim(trans('discount'), ' '); // Rem not space char (in French ip_lang & maybe other)
                ?></td>
                <td class="text-right"><?php echo $discount; ?></td>
            </tr>
<?php
    }
}

/**
 * Generate the PDF for an invoice.
 *
 * @param      $invoice_id
 * @param bool $stream
 *
 * @return string
 */
function generate_invoice_pdf($invoice_id, $stream = true, $invoice_template = null, $is_guest = null)
{
    $CI = & get_instance();

    $CI->load->model(
        [
            'invoices/mdl_items',
            'invoices/mdl_invoices',
            'invoices/mdl_invoice_tax_rates',
            'custom_fields/mdl_custom_fields',
            'payment_methods/mdl_payment_methods',
        ]
    );

    $CI->load->helper(['country', 'client']);

    $invoice = $CI->mdl_invoices->get_by_id($invoice_id);
    $invoice = $CI->mdl_invoices->get_payments($invoice);

    // Override system language with client language
    set_language($invoice->client_language);

    if ( ! $invoice_template) {
        $CI->load->helper('template');
        $invoice_template = select_pdf_invoice_template($invoice);
    }

    $payment_method = $CI->mdl_payment_methods->where('payment_method_id', $invoice->payment_method)->get()->row();
    if ($invoice->payment_method == 0) {
        $payment_method = false;
    }

    // Determine if discounts should be displayed
    $items = $CI->mdl_items->where('invoice_id', $invoice_id)->get()->result();

    // Discount settings
    $show_item_discounts = false;
    foreach ($items as $item) {
        if ($item->item_discount != '0.00') {
            $show_item_discounts = true;
            break;
        }
    }

    // Get all custom fields
    $custom_fields = [
        'invoice' => $CI->mdl_custom_fields->get_values_for_fields('mdl_invoice_custom', $invoice->invoice_id),
        'client'  => $CI->mdl_custom_fields->get_values_for_fields('mdl_client_custom', $invoice->client_id),
        'user'    => $CI->mdl_custom_fields->get_values_for_fields('mdl_user_custom', $invoice->user_id),
    ];

    if ($invoice->quote_id) {
        $custom_fields['quote'] = $CI->mdl_custom_fields->get_values_for_fields('mdl_quote_custom', $invoice->quote_id);
    }

    $filename = trans('invoice') . '_' . str_replace(['\\', '/'], '_', $invoice->invoice_number);

    // START eInvoicing
    $xml_id    = false;
    $embed_xml = false;
    // For embed file on PDF
    $associatedFiles = null;
    if (get_setting('einvoicing')) {
        $CI->load->helper('e-invoice');
        // Get eInvoice name (version) and user checks
        $einvoice = get_einvoice_usage($invoice, $items, false);
        // Set eInvoice config (false if Client & User not Ok)
        $xml_id  = $einvoice->user ? $einvoice->name : false;
        $options = [];
        // Same name of config & library(+Xml) by default
        $generator = $xml_id;
        $path      = APPPATH . 'helpers/XMLconfigs/';
        if ($xml_id && file_exists($path . $xml_id . '.php') && include $path . $xml_id . '.php') {
            $embed_xml = $xml_setting['embedXML'];
            $XMLname   = $xml_setting['XMLname'];
            $options   = (empty($xml_setting['options']) ? $options : $xml_setting['options']); // Optional
            $generator = (empty($xml_setting['generator']) ? $generator : $xml_setting['generator']); // Optional
        }

        if ($xml_id && $embed_xml) {
            // Create the CII XML file
            $associatedFiles = [[
                'path'           => generate_xml_invoice_file($invoice, $items, $generator, $filename, $options),
                'name'           => $_SERVER['CIIname'] ?? $XMLname,   // Get from generator or from config
                'mime'           => $_SERVER['CIImime'] ?? 'text/xml', // Get mime from generator
                'description'    => $xml_id . ' e-' . trans('invoice'),
                'AFRelationship' => 'Alternative',
            ]];
        }
    }

    $data = [
        'invoice'             => $invoice,
        'invoice_tax_rates'   => $CI->mdl_invoice_tax_rates->where('invoice_id', $invoice_id)->get()->result(),
        'items'               => $items,
        'payment_method'      => $payment_method,
        'output_type'         => 'pdf',
        'show_item_discounts' => $show_item_discounts,
        'custom_fields'       => $custom_fields,
        'legacy_calculation'  => config_item('legacy_calculation'),
    ];

    $html = $CI->load->view('invoice_templates/pdf/' . $invoice_template, $data, true);

    // Create PDF with or without an embedded XML
    $CI->load->helper('mpdf');

    $retval = pdf_create(
        html:             $html,
        filename:         $filename,
        stream:           $stream,
        password:         $invoice->invoice_password,
        isInvoice:        true,
        is_guest:         $is_guest,
        embed_xml:        $embed_xml,
        associated_files: $associatedFiles
    );

    if ($embed_xml && file_exists(UPLOADS_TEMP_FOLDER . $filename . '.xml')) {
        if (IP_DEBUG) {
            // To simplify xml validation Copy to uploads/temp/einvoice_test.xml. Same file but Always new (when get/send pdf)
            copy(UPLOADS_TEMP_FOLDER . $filename . '.xml', UPLOADS_TEMP_FOLDER . 'einvoice_test.xml');
        }
        // Delete the tmp CII-XML file
        unlink(UPLOADS_TEMP_FOLDER . $filename . '.xml');
    }

    // eInvoice Ok & not in pdf, create the UBL XML file
    if ($xml_id && $embed_xml !== true) {
        // Need Specific eInvoice filename?
        if ( ! empty($options['CIIname'])) {
            // The mailer helper parse tag(s) of this $_SERVER var & phpmailer attach the file with this (name).
            $_SERVER['CIIname'] = $options['CIIname'];
        }
        // Same name of pdf file for attachment work!
        $filename = date('Y-m-d') . '_' . $filename;
        generate_xml_invoice_file($invoice, $items, $generator, $filename, $options);
    }
    // END eInvoicing

    return $retval;
}

function generate_invoice_sumex($invoice_id, $stream = true, $invoice_template = null, $client = false)
{
    $CI = & get_instance();

    $CI->load->model('invoices/mdl_items');
    $invoice = $CI->mdl_invoices->get_by_id($invoice_id);
    $CI->load->library('Sumex', [
        'invoice' => $invoice,
        'items'   => $CI->mdl_items->where('invoice_id', $invoice_id)->get()->result(),
    ]);

    $sumexPDF = $CI->sumex->pdf($invoice_template);
    $sha1sum  = sha1($sumexPDF);
    $shortsum = mb_substr($sha1sum, 0, 8);
    $filename = trans('invoice') . '_' . str_replace(['\\', '/'], '_', $invoice->invoice_number) . '_' . $shortsum;

    if ( ! $client) {
        $temp = tempnam('/tmp', 'invsumex_');
        file_put_contents($temp, $sumexPDF);
        $pdf       = new \setasign\Fpdi\Fpdi();
        $pageCount = $pdf->setSourceFile($temp);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size       = $pdf->getTemplateSize($templateId);
            $pdf->addPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }

        unlink($temp);

        if ($stream) {
            header('Content-Type', 'application/pdf');
            $pdf->Output($filename . '.pdf', 'I');

            return;
        }

        $filePath = UPLOADS_TEMP_FOLDER . $filename . '.pdf';
        $pdf->Output($filePath, 'F');

        return $filePath;
    }
    if ($stream) {
        return $sumexPDF;
    }

    $filePath = UPLOADS_TEMP_FOLDER . $filename . '.pdf';
    file_put_contents($filePath, $sumexPDF);

    return $filePath;
}

/**
 * Generate the PDF for a quote.
 *
 * @param      $quote_id
 * @param bool $stream
 *
 * @return string
 *
 * @throws \Mpdf\MpdfException
 */
function generate_quote_pdf($quote_id, $stream = true, $quote_template = null)
{
    $CI = &get_instance();

    $CI->load->model(
        [
            'quotes/mdl_quotes',
            'quotes/mdl_quote_items',
            'quotes/mdl_quote_tax_rates',
            'custom_fields/mdl_custom_fields',
        ]
    );
    $CI->load->helper(
        [
            'country',
            'client',
        ]
    );

    $quote = $CI->mdl_quotes->get_by_id($quote_id);

    // Override language with system language
    set_language($quote->client_language);

    if ( ! $quote_template) {
        $quote_template = $CI->mdl_settings->setting('pdf_quote_template');
    }

    // Determine if discounts should be displayed
    $items = $CI->mdl_quote_items->where('quote_id', $quote_id)->get()->result();

    $show_item_discounts = false;
    foreach ($items as $item) {
        if ($item->item_discount != '0.00') {
            $show_item_discounts = true;
            break;
        }
    }

    // Get all custom fields
    $custom_fields = [
        'quote'  => $CI->mdl_custom_fields->get_values_for_fields('mdl_quote_custom', $quote->quote_id),
        'client' => $CI->mdl_custom_fields->get_values_for_fields('mdl_client_custom', $quote->client_id),
        'user'   => $CI->mdl_custom_fields->get_values_for_fields('mdl_user_custom', $quote->user_id),
    ];

    $data = [
        'quote'               => $quote,
        'quote_tax_rates'     => $CI->mdl_quote_tax_rates->where('quote_id', $quote_id)->get()->result(),
        'items'               => $items,
        'output_type'         => 'pdf',
        'show_item_discounts' => $show_item_discounts,
        'custom_fields'       => $custom_fields,
        'legacy_calculation'  => config_item('legacy_calculation'),
    ];

    $html = $CI->load->view('quote_templates/pdf/' . $quote_template, $data, true);

    $CI->load->helper('mpdf');

    return pdf_create($html, trans('quote') . '_' . str_replace(['\\', '/'], '_', $quote->quote_number), $stream, $quote->quote_password);
}
