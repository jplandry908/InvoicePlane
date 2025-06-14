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

#[AllowDynamicProperties]
class Mdl_Setup extends CI_Model
{
    public $errors = [];

    /**
     * @return bool
     */
    public function install_tables()
    {
        $file_contents = file_get_contents(APPPATH . 'modules/setup/sql/000_1.0.0.sql');

        $this->execute_contents($file_contents);

        $this->save_version('000_1.0.0.sql');

        if ($this->errors) {
            return false;
        }

        $this->install_default_data();

        $this->install_default_settings();

        return true;
    }

    public function install_default_data()
    {
        $this->db->insert('ip_invoice_groups', [
            'invoice_group_name'    => 'Invoice Default',
            'invoice_group_next_id' => 1,
        ]);

        $this->db->insert('ip_invoice_groups', [
            'invoice_group_name'    => 'Quote Default',
            'invoice_group_prefix'  => 'QUO',
            'invoice_group_next_id' => 1,
        ]);

        $this->db->insert('ip_payment_methods', [
            'payment_method_name' => 'Cash',
        ]);

        $this->db->insert('ip_payment_methods', [
            'payment_method_name' => 'Credit Card',
        ]);
    }

    /**
     * @return bool
     */
    public function upgrade_tables()
    {
        // Collect the available SQL files
        $sql_files = directory_map(APPPATH . 'modules/setup/sql', true);

        // Sort them so they're in natural order
        sort($sql_files);

        // Unset the installer
        unset($sql_files[0]);

        // Loop through the files and take appropriate action
        foreach ($sql_files as $sql_file) {
            if (mb_substr($sql_file, -4) !== '.sql') {
                continue;
            }

            $this->db->where('version_file', $sql_file);
            $update_applied = $this->db->get('ip_versions');

            if ($update_applied->num_rows()) {
                continue;
            }

            $file_contents = file_get_contents(APPPATH . 'modules/setup/sql/' . $sql_file);
            $this->execute_contents($file_contents);
            $this->save_version($sql_file);

            $upgrade_method = 'upgrade_' . str_replace('.', '_', mb_substr($sql_file, 0, -4));

            if ( ! method_exists($this, $upgrade_method)) {
                continue;
            }

            $this->{$upgrade_method}();
        }

        if ($this->errors) {
            return false;
        }

        $this->install_default_settings();

        return true;
    }

    /**
     * ===========================================
     * Place upgrade functions here
     * e.g. if table rows have to be converted
     * public function upgrade_010_1_0_1() { ... }.
     */
    public function upgrade_006_1_2_0()
    {
        /* Update alert to notify about the changes with invoice deletion and credit invoices
         * but only display the warning when the previous version is 1.1.2 or lower and it's an update
         * therefore check if it's an update, if the time difference between v1.1.2 and v1.2.0 is
         * greater than 100 and if v1.2.0 was not installed within this update process
         */
        $this->db->where_in('version_file', ['006_1.2.0.sql', '005_1.1.2.sql']);
        $versions     = $this->db->get('ip_versions')->result();
        $upgrade_diff = $versions[1]->version_date_applied - $versions[0]->version_date_applied;

        if ($this->session->userdata('is_upgrade') && $upgrade_diff > 100 && $versions[1]->version_date_applied > (time() - 100)) {
            $setup_notice = [
                'type'    => 'alert-danger',
                'content' => trans('setup_v120_alert'),
            ];
            $this->session->set_userdata('setup_notice', $setup_notice);
        }
    }

    public function upgrade_019_1_4_7()
    {
        /* Update alert to set the session configuration $config['sess_use_database'] = false to true
         * but only display the warning when the previous version is 1.4.6 or lower and it's an update
         * (see above for details)
         */
        $this->db->where_in('version_file', ['018_1.4.6.sql', '019_1.4.7.sql']);
        $versions     = $this->db->get('ip_versions')->result();
        $upgrade_diff = $versions[1]->version_date_applied - $versions[0]->version_date_applied;

        if ($this->session->userdata('is_upgrade') && $upgrade_diff > 100 && $versions[1]->version_date_applied > (time() - 100)) {
            $setup_notice = [
                'type'    => 'alert-danger',
                'content' => trans('setup_v147_alert'),
            ];
            $this->session->set_userdata('setup_notice', $setup_notice);
        }
    }

    public function upgrade_023_1_5_0()
    {
        $res          = $this->db->query('SELECT * FROM ip_custom_fields');
        $drop_columns = [];

        $tables = [
            'client',
            'invoice',
            'quote',
            'payment',
            'user',
        ];

        if ($res->num_rows()) {
            foreach ($res->result() as $row) {
                $drop_columns[] = [
                    'field_id' => $row->custom_field_id,
                    'column'   => $row->custom_field_column,
                    'table'    => $row->custom_field_table,
                ];
            }
        }

        // Create tables
        $this->db->query('CREATE TABLE `ip_client_custom_new`
            (
                `client_custom_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT ,
                `client_id` INT NOT NULL, `client_custom_fieldid` INT NOT NULL,
                `client_custom_fieldvalue` TEXT NULL ,
                UNIQUE (client_id, client_custom_fieldid)
            );');

        $this->db->query('CREATE TABLE `ip_invoice_custom_new`
            (
            `invoice_custom_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT ,
            `invoice_id` INT NOT NULL, `invoice_custom_fieldid` INT NOT NULL,
            `invoice_custom_fieldvalue` TEXT NULL ,
            UNIQUE (invoice_id, invoice_custom_fieldid)
            );');

        $this->db->query('CREATE TABLE `ip_quote_custom_new`
            (
                `quote_custom_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT ,
                `quote_id` INT NOT NULL, `quote_custom_fieldid` INT NOT NULL,
                `quote_custom_fieldvalue` TEXT NULL ,
                UNIQUE (quote_id, quote_custom_fieldid)
            );');

        $this->db->query('CREATE TABLE `ip_payment_custom_new`
            (
                `payment_custom_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT ,
                `payment_id` INT NOT NULL, `payment_custom_fieldid` INT NOT NULL,
                `payment_custom_fieldvalue` TEXT NULL ,
                UNIQUE (payment_id, payment_custom_fieldid)
            );');

        $this->db->query('CREATE TABLE `ip_user_custom_new`
            (
                `user_custom_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT ,
                `user_id` INT NOT NULL, `user_custom_fieldid` INT NOT NULL,
                `user_custom_fieldvalue` TEXT NULL ,
                UNIQUE (user_id, user_custom_fieldid)
            );');

        // Migrate Data
        foreach ($drop_columns as $value) {
            $res = $this->db->query('SELECT * FROM ' . $value['table']);

            preg_match('/^ip_(.*?)_custom$/i', $value['table'], $matches);
            $table_type = $matches[1];
            $table_name = $value['table'] . '_new';

            if ($res->num_rows()) {
                foreach ($res->result() as $row) {
                    $escaped_table_type = $this->db->escape($row->{$table_type . '_id'});
                    $escaped_column     = $this->db->escape($row->{$value['column']});

                    $query = "INSERT INTO {$table_name}
                        (" . $table_type . '_id, ' . $table_type . '_custom_fieldid, ' . $table_type . "_custom_fieldvalue)
                        VALUES (
                            {$escaped_table_type},
                            (
                                SELECT custom_field_id
                                FROM ip_custom_fields
                                WHERE ip_custom_fields.custom_field_column = " . $this->db->escape($value['column']) . "
                            ),
                            {$escaped_column}
                        )";

                    $this->db->query($query);
                }
            }
        }

        // Drop old cloumns, and rename new ones
        foreach ($tables as $table) {
            $this->db->query('DROP TABLE IF EXISTS `ip_' . $table . '_custom`');
            $query = 'RENAME TABLE `ip_' . $table . '_custom_new` TO `ip_' . $table . '_custom`';
            $this->db->query($query);
        }

        $this->db->query('ALTER TABLE ip_custom_fields DROP COLUMN custom_field_column');
    }

    public function upgrade_029_1_5_6()
    {
        // The following code will determine if the ip_users table has an existing user_all_clients column
        // If the table already has the column it will be shown in any user query, so get one now
        $test_user = $this->db->query('SELECT * FROM `ip_users` ORDER BY `user_id` ASC LIMIT 1')->row();

        // Add new user key if applicable
        if ( ! isset($test_user->user_all_clients)) {
            $this->db->query('ALTER TABLE `ip_users`
              ADD `user_all_clients` INT(1) NOT NULL DEFAULT 0
              AFTER `user_psalt`;');
        }

        // Copy the invoice pdf footer to the new quote pdf footer setting
        $this->load->model('settings/mdl_settings');
        $this->mdl_settings->load_settings();
        $this->load->helper('settings');

        $this->mdl_settings->save('pdf_quote_footer', get_setting('pdf_invoice_footer'));
    }

    public function upgrade_036_1_6()
    {
        //upgrade the recurring invoices data and replace 0000-00-00 invalid date with null in order to be compliant
        //with the MySQL >= 5.8 defautl SQL Strict mode that is activated by default.
        //migrate the dates data from 0000-00-00 to NULL in order to allow SQL Strict mode. Because the new
        //mysql default mode, the change must be done by PHP logic.

        //**recur_end_date**
        $rows_recur_end_date = $this->db->query('SELECT * FROM `ip_invoices_recurring`');
        foreach ($rows_recur_end_date->result() as $row) {
            if ($row->recur_end_date == '0000-00-00') {
                $this->db->set('recur_end_date', null)->where('invoice_recurring_id', $row->invoice_recurring_id)->update('ip_invoices_recurring');
            }

            if ($row->recur_next_date == '0000-00-00') {
                $this->db->set('recur_next_date', null)->where('invoice_recurring_id', $row->invoice_recurring_id)->update('ip_invoices_recurring');
            }
        }

        //**client_bdate**
        $rows_client_bdate = $this->db->query('SELECT * FROM `ip_clients`');
        foreach ($rows_client_bdate->result() as $row_bdate) {
            if ($row_bdate->client_birthdate == '0000-00-00') {
                $this->db->set('client_birthdate', null)->where('client_id', $row_bdate->client_id)->update('ip_clients');
            }
        }
    }

    public function upgrade_039_1_6_3()
    {
        //**Set languages to lowercase & replace include_zugferd setting to einvoicing**
        $einvoicing = '0';
        $step       = 2;
        $rows       = $this->db->query('SELECT * FROM `ip_settings`');
        foreach ($rows->result() as $row) {
            // Set default_language to lowercase
            if ($row->setting_key == 'default_language') {
                $this->db->set('setting_value', mb_strtolower($row->setting_value))->where('setting_id', $row->setting_id)->update('ip_settings');
                $step--;
            }

            // include_zugferd > einvoicing
            if ($row->setting_key == 'include_zugferd') {
                $einvoicing = $row->setting_value;
                $this->db->set('setting_key', 'einvoicing')->where('setting_id', $row->setting_id)->update('ip_settings');
                $step--;
            }

            // All Steps Ok
            if ( ! $step) {
                break;
            }
        }

        // Set all users languages to lowercase
        $rows = $this->db->query('SELECT * FROM `ip_users`');
        foreach ($rows->result() as $row) {
            if ($row->user_language != 'system') {
                $this->db->set('user_language', mb_strtolower($row->user_language))->where('user_id', $row->user_id)->update('ip_users');
            }
        }

        if ($einvoicing == '1') {
            // einvoicing is on, Enable Zugferd v1.0 for all clients
            $data = ['client_einvoicing_active' => '1', 'client_einvoicing_version' => 'Zugferdv10'];
            $rows = $this->db->query('SELECT * FROM `ip_clients`');
            foreach ($rows->result() as $row) {
                if ($row->client_active == '1') {
                    $this->db->update('ip_clients', $data, ['client_id' => $row->client_id]);
                }
            }
        } else {
            // Delete Zugferd lib & conf
            $filename = 'Zugferdv10';
            $files[]  = APPPATH . 'libraries/XMLtemplates/' . $filename . 'Xml.php';
            $files[]  = APPPATH . 'helpers/XMLconfigs/' . $filename . '.php';
            foreach ($files as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * @param string $contents
     */
    private function execute_contents(string|bool $contents)
    {
        $commands = explode(';', $contents);

        foreach ($commands as $command) {
            if ( ! mb_trim($command)) {
                continue;
            }

            $this->db->db_debug = IP_DEBUG;

            $this->db->query(mb_trim($command) . ';');

            $error = $this->db->error();
            if ($error['code'] !== 0) {
                $this->errors[] = $error['message'];
            }
        }
    }

    /**
     * @param $sql_file
     */
    private function save_version($sql_file)
    {
        $version_db_array = [
            'version_date_applied' => time(),
            'version_file'         => $sql_file,
            'version_sql_errors'   => count($this->errors),
        ];

        $this->db->insert('ip_versions', $version_db_array);
    }

    private function install_default_settings()
    {
        $this->load->helper('string');

        $default_settings = [
            'default_language'             => $this->session->userdata('ip_lang'),
            'date_format'                  => 'm/d/Y',
            'currency_symbol'              => '$',
            'currency_symbol_placement'    => 'before',
            'currency_code'                => 'USD',
            'invoices_due_after'           => 30,
            'quotes_expire_after'          => 15,
            'default_invoice_group'        => 3,
            'default_quote_group'          => 4,
            'thousands_separator'          => ',',
            'decimal_point'                => '.',
            'cron_key'                     => random_string('alnum', 16),
            'tax_rate_decimal_places'      => 2,
            'pdf_invoice_template'         => 'InvoicePlane',
            'pdf_invoice_template_paid'    => 'InvoicePlane - paid',
            'pdf_invoice_template_overdue' => 'InvoicePlane - overdue',
            'pdf_quote_template'           => 'InvoicePlane',
            'public_invoice_template'      => 'InvoicePlane_Web',
            'public_quote_template'        => 'InvoicePlane_Web',
            'disable_sidebar'              => 1,
        ];

        foreach ($default_settings as $setting_key => $setting_value) {
            $this->db->where('setting_key', $setting_key);

            if ( ! $this->db->get('ip_settings')->num_rows()) {
                $db_array = [
                    'setting_key'   => $setting_key,
                    'setting_value' => $setting_value,
                ];

                $this->db->insert('ip_settings', $db_array);
            }
        }
    }
}
