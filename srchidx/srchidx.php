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

if (!defined('_PS_VERSION_')) {
    exit;
}

class SrchIdx extends Module
{
    
    public function __construct()
    {
        $this->name = 'srchidx';
        $this->displayName = $this->l('Search Indexer');
        $this->description = $this->l('Rebuilds the search index sequentially to avoid timeouts');
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'KaisarCode';
        
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->path = realpath(dirname(__FILE__));
        $this->ps_version = Configuration::get('PS_VERSION_DB');
        $this->ps_version = explode('.', $this->ps_version);
        $this->ps_version = $this->ps_version[0].$this->ps_version[1];
        $this->ps_versions_compliancy = array(
            'min' => '1.6',
            'max' => _PS_VERSION_
        );
        
        parent::__construct();
    }
    
    // DATA USED BY MODULE
    public function setUp()
    {
        $lnk = new Link();
        $data = new stdClass();
        $data->mod = $this;
        $data->pth = $this->path;
        $data->key = $this->getModSkey();
        $data->ctx = Context::getContext();
        $data->shp = $data->ctx->shop->id;
        $data->lng = $data->ctx->language->id;
        $data->ssl = Tools::usingSecureMode();
        $data->url = new stdClass();
        $data->url->api = $lnk->getModuleLink($this->name, 'api', [], $data->ssl);
        return $data;
    }
    
    // CONFIG PAGE
    public function getContent()
    {
        $data = $this->setUp();
        return $this->displayTpl('admin/config', $data);
    }
    
    // INSTALL MODULE
    public function install()
    {
        parent::install();
        return true;
    }
    
    // UNINSTALL MODULE
    public function uninstall()
    {
        parent::uninstall();
        return true;
    }
    
    // MODULE SECURE KEY
    private function getModSkey()
    {
        $skey = Configuration::get('SRCHIDX_SKEY');
        if (!$skey) {
            $skey = md5(_COOKIE_KEY_.uniqid());
            Configuration::updateValue('SRCHIDX_SKEY', $skey);
        }
        return $skey;
    }
    
    // DISPLAY TEMPLATE
    private function displayTpl($tpl, $data = null)
    {
        $name = $this->name;
        $this->context->smarty->assign('data', $data);
        if ($this->ps_version < 17) {
            return $this->display(__FILE__, "/views/templates/$tpl.tpl");
        } else {
            return $this->fetch("module:$name/views/templates/$tpl.tpl");
        }
    }
}
