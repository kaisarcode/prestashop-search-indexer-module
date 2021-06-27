{**
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
*}

<div class="row">
    <div class="col-lg-6">
        
        <div class="panel panel-default form-horizontal">
            <div class="panel-header">
                <div class="panel-heading">
                    <i class="icon-cogs"></i>
                    <span>{l s='Rebuild search index' mod='srchidx'}</span>
                </div>
            </div>
            <div class="form-wrapper">
                <div class="form-group">
                    
                    <p><small><em id="srchidx-txt">&nbsp;</em></small></p>
                    <div class="progress">
                        <div id="srchidx-bar" class="progress-bar" role="progressbar" style="width: 0;">
                            <span id="srchidx-per">0%</span>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="panel-footer">
                <button id="srchidx-btn" type="button" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i>
                    <span>{l s='Start now' mod='srchidx'}</span>
                </button>
            </div>
        </div>
        
    </div>
</div>
<script>
(function(){
    
    var idxes = 0; var idxcn = 0;
    var words = 0; var wrdcn = 0;
    var prods = 0; var prdcn = 0;
    var items = 0; var itmcn = 0;
    
    var mgs = [];
    var key = '{$data->key}';
    var api = '{$data->url->api}';
    var txt = $('#srchidx-txt');
    var bar = $('#srchidx-bar');
    var per = $('#srchidx-per');
    var btn = $('#srchidx-btn');
    
    btn.on('click', function(){
        
        idxes = 0; idxcn = 0;
        words = 0; wrdcn = 0;
        prods = 0; prdcn = 0;
        items = 0; itmcn = 0;
        
        setUI(1, 0, mgs[0]);
        post('count-index', 0, function(res){
            idxes = res;
            items += res;
            setUI(1, 0, mgs[1]);
            post('count-words', 0, function(res){
                words = res;
                items += res;
                setUI(1, 0, mgs[2]);
                post('count-prods', 0, function(res){
                    prods = res;
                    items += res;
                    clearIndex(0, 0);
                });
            });
        });
    });
    
    function clearIndex(ofs, prg) {
        setUI(1, prg, mgs[3]);
        var prm = {};
        prm.ofs = ofs;
        prm.lmt = 1;
        if (idxes > 50) prm.lmt = 10;
        if (idxes > 100) prm.lmt = 50;
        if (idxes > 1000) prm.lmt = 100;
        if (idxes > 10000) prm.lmt = 500;
        post('clear-index', prm, function(res){
            idxcn += res.cn;
            itmcn += res.cn;
            prg = (itmcn*100)/items;
            prg = Math.floor(prg);
            prg >= 100 ? prg = 100:'';
            if (idxcn >= idxes) {
                clearWords(0, prg);
            } else {
                clearIndex(0, prg);
            }
        });
    }
    
    function clearWords(ofs, prg) {
        setUI(1, prg, mgs[4]);
        var prm = {};
        prm.ofs = ofs;
        prm.lmt = 1;
        if (words > 50) prm.lmt = 10;
        if (words > 100) prm.lmt = 50;
        if (words > 1000) prm.lmt = 100;
        if (words > 10000) prm.lmt = 500;
        post('clear-words', prm, function(res){
            wrdcn += res.cn;
            itmcn += res.cn;
            prg = (itmcn*100)/items;
            prg = Math.floor(prg);
            prg >= 100 ? prg = 100:'';
            if (wrdcn >= words) {
                indexProds(0, prg);
            } else {
                clearWords(0, prg);
            }
        });
    }
    
    function indexProds(ofs, prg) {
        setUI(1, prg, mgs[5]);
        var prm = {};
        prm.ofs = ofs;
        prm.lmt = 1;
        if (prods > 50) prm.lmt = 10;
        if (prods > 100) prm.lmt = 50;
        if (prods > 1000) prm.lmt = 100;
        if (prods > 10000) prm.lmt = 500;
        post('index-prods', prm, function(res){
            prdcn += res.cn;
            itmcn += res.cn;
            prg = (itmcn*100)/items;
            prg = Math.floor(prg);
            prg >= 100 ? prg = 100:'';
            if (prdcn >= prods) {
                setUI(0, prg, mgs[6]);
            } else {
                indexProds(itmcn, prg);
            }
        });
    }
    
    function setUI(ds, pr, tx) {
        ds = ds || 0;
        pr = pr || 0;
        tx = tx || "&nbsp";
        btn.prop('disabled', ds);
        bar.css('width', pr+"%");
        per.html(pr+"%");
        tx !== 0 && txt.html(tx);
    }
    function post(a, dt, cb) {
        cb = cb || function(){};
        dt = dt || {};
        dt.a = a;
        dt.k = key;
        $.post(api, dt, function(res){
            if (res.ok) { cb(res.res);
            } else { setUI(0, 0, mgs[8]); }
        });
    }
    
    mgs.push("{l s='Obtaining indexes' mod='srchidx'}");
    mgs.push("{l s='Obtaining words' mod='srchidx'}");
    mgs.push("{l s='Obtaining products' mod='srchidx'}");
    
    mgs.push("{l s='Cleaning up index' mod='srchidx'}");
    mgs.push("{l s='Cleaning up words' mod='srchidx'}");
    
    mgs.push("{l s='Indexing' mod='srchidx'}");
    mgs.push("{l s='Finished!' mod='srchidx'}");
    mgs.push("{l s='Error' mod='srchidx'}");
})();
</script>
