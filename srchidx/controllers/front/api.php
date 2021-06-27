<?php
/**
* 2007 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    KaisarCode <info@kaisarcode.com>
*  @copyright 2021 KaisarCode
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class SrchIdxApiModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $display_column_right = false;
    public function initContent()
    {
        parent::initContent();
        $this->ajax = true;
    }
    
    public function displayAjax()
    {
        header('Content-Type: application/json');
        $data = $this->module->setUp();
        $act = Tools::getValue('a');
        $key = Tools::getValue('k');
        
        // PREPARE OUTPUT
        $out = new stdClass();
        $out->ok = 0;
        $out->res = null;
        
        // PROCESS REQUESTS
        if ($key = $data->key) {
            
            // Count search index
            if ($act == 'count-index') {
                $out->ok = 1;
                $out->res = $this->countTotals('search_index');
            }
            
            // Count words
            if ($act == 'count-words') {
                $out->ok = 1;
                $out->res = $this->countTotals('search_word');
            }
            
            // Count prods
            if ($act == 'count-prods') {
                $out->ok = 1;
                $out->res = $this->countTotals('product_shop');
            }
            
            // Clear indexes
            if ($act == 'clear-index') {
                $out->ok = 1;
                $tbl = 'search_index';
                $key = 'id_word';
                $ofs = (int) Tools::getValue('ofs');
                $lmt = (int) Tools::getValue('lmt');
                $out->res = $this->delRows($tbl, $key, $ofs, $lmt);
            }
            
            // Clear words
            if ($act == 'clear-words') {
                $out->ok = 1;
                $tbl = 'search_word';
                $key = 'id_word';
                $ofs = (int) Tools::getValue('ofs');
                $lmt = (int) Tools::getValue('lmt');
                $out->res = $this->delRows($tbl, $key, $ofs, $lmt);
            }
            
            // Clear products
            if ($act == 'index-prods') {
                $out->ok = 1;
                $ofs = (int) Tools::getValue('ofs');
                $lmt = (int) Tools::getValue('lmt');
                $out->res = $this->indexProds($ofs, $lmt);
            }
        }
        
        // SHOW OUTPUT
        $data = Tools::jsonEncode($out);
        echo $data;
    }
    
    private function delRows($tbl, $key, $ofs, $lmt)
    {
        $out = new stdClass();
        $dbx = _DB_PREFIX_;
        $dbi = DB::getInstance();
        $sql = "
        SELECT $key AS id
        FROM {$dbx}{$tbl}
        ORDER BY id ASC
        LIMIT $lmt OFFSET $ofs;";
        $res = $dbi->executeS($sql);
        $out->id = $ofs;
        $out->cn = count($res)+1;
        foreach ($res as $r) {
            $id = $r['id'];
            $out->id = (int) $id;
            $dbi->delete($tbl, "$key = $id");
        }
        return $out;
    }
    
    // CLEAR PRODUCTS
    private function indexProds($ofs, $lmt)
    {
        $out = new stdClass();
        $dbx = _DB_PREFIX_;
        $dbi = DB::getInstance();
        $sql = "
        SELECT id_product AS id
        FROM {$dbx}product_shop
        ORDER BY id ASC
        LIMIT $lmt OFFSET $ofs;";
        $res = $dbi->executeS($sql);
        $out->id = $ofs;
        $out->cn = count($res)+1;
        foreach ($res as $r) {
            $id = $r['id'];
            $out->id = (int) $id;
            Search::indexation(0, $id);
        }
        return $out;
    }
    
    // COUNT TOTALS
    private function countTotals($tbl)
    {
        $dbx = _DB_PREFIX_;
        $dbi = DB::getInstance();
        $sql = "
        SELECT COUNT(*) AS t
        FROM {$dbx}{$tbl};";
        $res = $dbi->executeS($sql);
        return (int) $res[0]['t'];
    }
}
