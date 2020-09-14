<div style="height: 140px;">
    <form name="password-entry-form_2" id="password-entry-form_2" method="post" enctype="multipart/form-data">        
        <table style="width: 100%;" class="default-table boxtype">
            <input type="hidden" id="users_id" name="users_id" value="<?=$user_id?>">
            <col width="30%">
            <col width="70%">
            <thead>
                <tr>
                    <th colspan="2"><?=$fullname;?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Password:
                        <span class="red font-large">*</span>
                    </td>
                    <td>
                        <input id="password" class="input-text" type="password" value="" name="password">
                    </td>
                </tr>
                <tr>
                    <td>
                        Confirm:
                    </td>
                    <td>
                         <input id="password-confirm" class="input-text" type="password" value="" name="password-confirm">
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:center;">
                         <div class="icon-label-group">
                            <div class="icon-label">
                                <a onclick="save_password();" href="javascript:void(0);" class="icon-16-add" rel="record-save">
                                    <span>Save</span>
                                </a>            
                            </div>
                        </div>
                        <div class="or-cancel">
                            <span class="or">or</span>
                            <a rel="" href="javascript:void(0)" class="cancel" id="cancel_me" onclick="Boxy.get(this).hide().unload();">Cancel</a>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>