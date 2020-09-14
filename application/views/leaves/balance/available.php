    <div class="spacer"></div>
    
    <div class="form-item view odd ">
        <h4>Available</h4>
    </div>
    <div class="spacer"></div>
    <div class="form-item view odd ">
    <label for="vl" class="label-desc view gray">VL</label>
    <div class="text-input-wrap" id="vl_balance"><?=(isset($balance)) ? $balance->vl - $balance->vl_used : ''?></div>
    </div>  

    <div class="form-item view odd ">
    <label for="vl" class="label-desc view gray">SL</label>
    <div class="text-input-wrap" id="sl_balance"><?=(isset($balance)) ? $balance->sl - $balance->sl_used : ''?></div>
    </div>  

    <div class="form-item view odd ">
    <label for="vl" class="label-desc view gray">EL</label>
    <div class="text-input-wrap" id="el_balance"><?=(isset($balance)) ? $balance->el - $balance->el_used : ''?></div>
    </div>  

    <div class="form-item view odd ">
    <label for="vl" class="label-desc view gray">ML or PL</label>
    <div class="text-input-wrap" id="mpl_balance"><?=(isset($balance)) ? $balance->mpl - $balance->mpl_used : ''?></div>
    </div>  

    <div class="form-item view odd ">
    <label for="vl" class="label-desc view gray">BL</label>
    <div class="text-input-wrap" id="bl_balance"><?=(isset($balance)) ? $balance->bl - $balance->bl_used : ''?></div>
    </div>  