<form class="" id="form-jobsearch">   
    <div class="spacer"></div>
    <input type="hidden" name="search" value="1" />
    <div class="jobsearch-span1">
        <label class="label-desc gray">Gender</label>
        <div class="select-input-wrap">
            <select id="sex" name="sex">
                <option value=""></option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>
        </div>
    </div>
    <div class="jobsearch-span1">
        <label class="label-desc gray">Status</label>
        <div class="select-input-wrap">
            <select id="civil_status" name="civil_status">
                <option value=""></option>
                <option value="Single">Single</option>
                <option value="Married">Married</option>
                <option value="Widowed">Widowed</option>
            </select>
        </div>
    </div>
    <div class="jobsearch-span1">
        <label class="label-desc gray">Age</label>
        <div class="text-input-wrap">
            <input type="text" name="age" />
        </div>
    </div>
    <div class="spacer"></div>
    <div class="jobsearch-span1">
        <label class="label-desc gray">Education</label>
        <div class="text-input-wrap">
            <input type="text" name="education" />
        </div>
    </div>
    <div class="jobsearch-span1">
        <label class="label-desc gray">Field</label>
        <div class="select-input-wrap"><select id="position_id" name="position_id">
            </select>
        </div>
    </div>
    <div class="jobsearch-span1">
        <label class="label-desc gray">Career</label>
        <div class="select-input-wrap"><select id="position_id" name="position_id">
            </select>
        </div>
    </div>
    <div class="spacer"></div>
    <div class="jobsearch-span3">
        <label class="label-desc gray">Others</label>
        <div class="text-input-wrap"><input type="text" class="input-text text-right" value=""></div>
    </div>            
    <div class="spacer"></div>
        

    <div class="jobsearch-span3">
        <label class="label-desc gray"></label>
        <div class="text-input-wrap">
            <div class="icon-label-group">        
                <div class="icon-label">            
                    <a href="javascript:void(0)" module_link="admin/template_manager" container="<?php echo $container;?>" class="icon-16-search jqgrid-advanced-search">
                        <span>Search</span>
                    </a>
                </div>            
            </div>            
        </div>
    </div>
    <div class="spacer"></div>
</form>