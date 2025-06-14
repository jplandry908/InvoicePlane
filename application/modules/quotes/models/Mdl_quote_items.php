<?php

if ( ! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * InvoicePlane
 *
 * @author		InvoicePlane Developers & Contributors
 * @copyright	Copyright (c) 2012 - 2018 InvoicePlane.com
 * @license		https://invoiceplane.com/license.txt
 * @link		https://invoiceplane.com
 */

#[AllowDynamicProperties]
class Mdl_Quote_Items extends Response_Model
{
    public $table = 'ip_quote_items';

    public $primary_key = 'ip_quote_items.item_id';

    public $date_created_field = 'item_date_added';

    public function default_select()
    {
        $this->db->select('ip_quote_item_amounts.*, ip_products.*, ip_quote_items.*,
            item_tax_rates.tax_rate_percent AS item_tax_rate_percent,
            item_tax_rates.tax_rate_name AS item_tax_rate_name');
    }

    public function default_order_by()
    {
        $this->db->order_by('ip_quote_items.item_order');
    }

    public function default_join()
    {
        $this->db->join('ip_quote_item_amounts', 'ip_quote_item_amounts.item_id = ip_quote_items.item_id', 'left');
        $this->db->join('ip_tax_rates AS item_tax_rates', 'item_tax_rates.tax_rate_id = ip_quote_items.item_tax_rate_id', 'left');
        $this->db->join('ip_products', 'ip_products.product_id = ip_quote_items.item_product_id', 'left');
    }

    /**
     * @return array
     */
    public function validation_rules()
    {
        return [
            'quote_id' => [
                'field' => 'quote_id',
                'label' => trans('quote'),
                'rules' => 'required',
            ],
            'item_sku' => [
                'field' => 'item_sku',
                'label' => trans('item_sku'),
                'rules' => 'required|unique',
            ],
            'item_name' => [
                'field' => 'item_name',
                'label' => trans('item_name'),
                'rules' => 'required',
            ],
            'item_description' => [
                'field' => 'item_description',
                'label' => trans('description'),
            ],
            'item_quantity' => [
                'field' => 'item_quantity',
                'label' => trans('quantity'),
            ],
            'item_price' => [
                'field' => 'item_price',
                'label' => trans('price'),
            ],
            'item_tax_rate_id' => [
                'field' => 'item_tax_rate_id',
                'label' => trans('item_tax_rate'),
            ],
            'item_product_id' => [
                'field' => 'item_product_id',
                'label' => trans('original_product'),
            ],
        ];
    }

    /**
     * @param []   $global_discount
     *
     * @return int|null
     */
    public function save($id = null, $db_array = null, &$global_discount = [])
    {
        $id = parent::save($id, $db_array);

        $this->load->model([
            'quotes/mdl_quote_item_amounts',
            'quotes/mdl_quote_amounts',
        ]);
        $this->mdl_quote_item_amounts->calculate($id, $global_discount);

        if (is_object($db_array) && isset($db_array->quote_id)) {
            $this->mdl_quote_amounts->calculate($db_array->quote_id, $global_discount);
        } elseif (is_array($db_array) && isset($db_array['quote_id'])) {
            $this->mdl_quote_amounts->calculate($db_array['quote_id'], $global_discount);
        }

        return $id;
    }

    /**
     * @param int $item_id
     */
    public function delete($item_id): bool
    {
        // Get item:
        // the quote id is needed to recalculate quote amounts
        $query = $this->db->get_where($this->table, ['item_id' => $item_id]);

        if ($query->num_rows() == 0) {
            return false;
        }

        $row      = $query->row();
        $quote_id = $row->quote_id;

        // Delete the item itself
        parent::delete($item_id);

        // Delete the item amounts
        $this->db->where('item_id', $item_id);
        $this->db->delete('ip_quote_item_amounts');

        $this->load->model('quotes/mdl_quote_amounts');
        $global_discount['item'] = $this->mdl_quote_amounts->get_global_discount($quote_id);
        // Recalculate quote amounts
        $this->mdl_quote_amounts->calculate($quote_id, $global_discount);

        return true;
    }

    /**
     * legacy_calculation false: Need to recalculate quote amounts - since v1.6.3.
     *
     * @param $quote_id
     *
     * return items_subtotal
     */
    public function get_items_subtotal($quote_id)
    {
        $row = $this->db->query('
            SELECT SUM(item_subtotal) AS items_subtotal
            FROM ip_quote_item_amounts
            WHERE item_id
                IN (SELECT item_id FROM ip_quote_items WHERE quote_id = ' . $this->db->escape($quote_id) . ')
            ')
            ->row();

        return $row->items_subtotal;
    }
}
