<?=form_open_multipart('employee/manual_dialed_hours_entry/validate_file', 'id="import-form"')?>
	<fieldset>	
		<input type="hidden" name="module_id" value="<?=$module_id?>" />
		<label>Select file (xls) :</label>
		<input type="file" name="import_file" size="20"/>
		<input type="submit" value="Import" />
	</fieldset>
</form>