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
            
            // Preprocess
            if ($act == 'clear') {
                $out->ok = 1;
                $out->res = $this->procClr();
            }
            
            // Process index
            if ($act == 'index') {
                $out->ok = 1;
                $pid = (int) Tools::getValue('p');
                $idx = (int) Tools::getValue('i');
                $amn = (int) Tools::getValue('m');
                $out->res = $this->procIdx($pid, $idx, $amn);
            }
            
        }
        
        // SHOW OUTPUT
        $data = Tools::jsonEncode($out);
        echo $data;
    }
    
    // PROCESS CLEANUP
    private function procClr()
    {
        $dbx = _DB_PREFIX_;
        $dbi = DB::getInstance();
        $dbi->delete('search_word', 1);
        $dbi->delete('search_index', 1);
        $dbi->update('product_shop', array(
            'indexed' => 0
        ), 1);
        $cnt = $this->countProds();
        return $cnt;
    }
    
    // PROCESS INDEX
    private function procIdx($pid = 0, $idx = 0, $amn = 1)
    {
        $dbx = _DB_PREFIX_;
        $pid = (int) $pid;
        $idx = (int) $idx;
        $amn = (int) $amn;
        $out = new stdClass();
        $dbi = DB::getInstance();
        $cnt = $this->countProds();
        $sql = "
        SELECT id_product AS id
        FROM {$dbx}product_shop
        ORDER BY id_product ASC
        LIMIT $pid, $amn;";
        $res = $dbi->executeS($sql);
        foreach ($res as $r) {
            $idx++;
            $out->idx = $idx;
            $out->pid = (int) $r['id'];
            Search::indexation(0, $pid);
        }
        return $out;
    }
    
    // COUNT PRODUCTS
    private function countProds()
    {
        $dbx = _DB_PREFIX_;
        $dbi = DB::getInstance();
        $sql = "
        SELECT COUNT(*) AS t
        FROM {$dbx}product_shop;";
        $res = $dbi->executeS($sql);
        return (int) $res[0]['t'];
    }
}
