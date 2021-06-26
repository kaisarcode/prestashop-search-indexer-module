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
    var pid = 0;
    var idx = 0;
    var tot = 0;
    var mgs = [];
    var key = '{$data->key}';
    var api = '{$data->url->api}';
    var txt = $('#srchidx-txt');
    var bar = $('#srchidx-bar');
    var per = $('#srchidx-per');
    var btn = $('#srchidx-btn');
    btn.on('click', function(){
        pid = 0;
        idx = 0;
        tot = 0;
        setUI(1, 0, mgs[0]);
        post('clear', 0, function(res){
            tot = res;
            setTimeout(procIdx,1000);
        });
    });
    function procIdx() {
        var prm = {};
        prm.p = pid;
        prm.i = idx;
        prm.m = 1;
        if (tot > 50) prm.m = 10;
        if (tot > 100) prm.m = 50;
        if (tot > 1000) prm.m = 100;
        post('index', prm, function(res){
            console.log(res);
            idx = res.idx;
            pid = pid + 1;
            prg = (idx * 100) / tot;
            prg = Math.floor(prg);
            prg >= 100 ? prg = 100:'';
            if (idx >= tot) {
                setUI(0, 100, mgs[3]);
            } else {
                setUI(1, prg, mgs[2]);
                procIdx();
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
            } else { setUI(0, 0, mgs[1]); }
        });
    }
    mgs.push("{l s='Cleaning up...' mod='srchidx'}");
    mgs.push("{l s='Error' mod='srchidx'}");
    mgs.push("{l s='Indexing' mod='srchidx'}");
    mgs.push("{l s='Finished!' mod='srchidx'}");
})();
</script>
