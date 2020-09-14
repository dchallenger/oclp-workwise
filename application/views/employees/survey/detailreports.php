<style>
table.padded-table td { 
	padding:10px; 
}
table.padded-table td select {
	width:150px;
}
</style>
<table class="padded-table">
<tr>
	<td>
		Company: &nbsp;
	</td>
	<td>
<select id="company" style="width:140%">
<?php
	$company=$this->db->get('user_company')->result_array();
	echo "<option value='0'>Select...</option>";
	foreach($company as $company_name)
	{
		echo "<option value='".$company_name['company_id']."'>".$company_name['company']."</option>";
	}
?>
</select>
	</td>
</tr>
<tr>
	<td>
		Department: &nbsp;
	</td>
	<td>
<select id="department" style="width:140%">
<?php
	$company=$this->db->get('user_company_department')->result_array();
	echo "<option value='0'>Select...</option>";
	foreach($company as $company_name)
	{
		echo "<option value='".$company_name['department_id']."'>".$company_name['department']."</option>";
	}
?>
</select>
	</td>
</tr>
<tr>
	<td>
		Segment_1: &nbsp;
	</td>
	<td>
<select id="segment_1" style="width:140%">
<?php
	$company=$this->db->get('user_company_segment_1')->result_array();
	echo "<option value='0'>Select...</option>";
	foreach($company as $company_name)
	{
		echo "<option value='".$company_name['segment_1_id']."'>".$company_name['segment_1']."</option>";
	}
?>
</select>
	</td>
</tr>
<tr>
	<td>
		Segment_2: &nbsp;
	</td>
	<td>
<select id="segment_2" style="width:140%">
<?php
	$company=$this->db->get('user_company_segment_2')->result_array();
	echo "<option value='0'>Select...</option>";
	foreach($company as $company_name)
	{
		echo "<option value='".$company_name['segment_2_id']."'>".$company_name['segment_2']."</option>";
	}
?>
</select>
	</td>
</tr>
<tr>
	<td>
		Division: &nbsp;
	</td>
	<td>
<select id="division" style="width:140%">
<?php
	$company=$this->db->get('user_company_division')->result_array();
	echo "<option value='0'>Select...</option>";
	foreach($company as $company_name)
	{
		echo "<option value='".$company_name['division_id']."'>".$company_name['division']."</option>";
	}
?>
</select>
	</td>
</tr>
</table>
&nbsp; <input type="button" onClick="get_report()" value="Get!"/>
<br />
<br />
<div class="put_me_here">

</div>
<br />
<br />
<?php
	$this->db->where('employee_ufs_main_id',$this->input->post('record_id'));
	$companywide_count=$this->db->get('employee_ufs_answer')->result_array();
	for($x=1;$x<=14;$x++)
	{
		$ctr=0;
		foreach($companywide_count as $companywide_add)
		{

			if($companywide_add['question_number']==$x)
			{
				if($companywide_add['answer']=='A')
				{
					$companywide_reports[$x]['A']=$companywide_reports[$x]['A']+1;
					$ctr++;
				}
				if($companywide_add['answer']==='B')
				{
					$companywide_reports[$x]['B']=$companywide_reports[$x]['B']+1;
					$ctr++;
				}
				if($companywide_add['answer']==='C')
				{
					$companywide_reports[$x]['C']=$companywide_reports[$x]['C']+1;
					$ctr++;
				}
				if($companywide_add['answer']==='D')
				{
					$companywide_reports[$x]['D']=$companywide_reports[$x]['D']+1;
					$ctr++;
				}
				if($companywide_add['answer']==='E')
				{
					$companywide_reports[$x]['E']=$companywide_reports[$x]['E']+1;
					$ctr++;
				}
				if($companywide_add['comment']!="")
				{
					$companywide_reports[$x]['comm'].="*".$companywide_add['comment']."<br />";
				}
			}
		}

		$numofresponses[$x]=$ctr;
	}
?>
<style>
table.padded-table td { 
	padding:10px; 
}
</style>
<table width="100%" class="padded-table prev" style="text-align:center;">
<th style="text-align:left;">Question</th><th style="border: solid 1px">Strongly Disagree</th><th style="border: solid 1px">&nbsp;Disagree&nbsp;</th><th style="border: solid 1px">Neither Agree nor Disagree</th><th style="border: solid 1px">&nbsp;Agree&nbsp;</th><th style="border: solid 1px">Strongly Agree</th><th style="border: solid 1px; ">No. of Responses</th><th style="border: solid 1px">Comment/s if any</th>
<tr><td style="text-align:left;font-style:italic">When I wear the euromoney pioneer T-Shirts...</td></tr>
<?php
	$txtctr=1;
	$txtquestion[1]="I feel excited and energetic";
	$txtquestion[2]="I feel Greater Sense of pride for Pioneer";
	$txtquestion[3]="I feel comfortable and relaxed when I wear them";
	$txtquestion[4]="I feel happy and confident about myself";
	$txtquestion[5]="I feel very professional when i'm with clients";
	$txtquestion[6]="I feel more cautious about how i carry myself because I carry the name Pioneer";
	$txtquestion[7]="I feel inspired at work";
	for($x=1;$x<=14;$x++)
	{
		if($txtctr==8) 
		{
			echo '<tr><td style="text-align:left;font-style:italic">When I wear the new set of corporate attire...</td></tr>';
			$txtctr=1;
		}
?>
							<tr>
								<td width="35%" style="text-align:left"><?= $x.". ".$txtquestion[$txtctr] ?></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<?php 
										if($companywide_reports[$x]['A']!="")
										{
											echo $companywide_reports[$x]['A']." = "; 
											echo round($companywide_reports[$x]['A'] * 100 / $numofresponses[$x])."%";
										}
									?>
								</td>
								<td style="border: solid 1px">
									<?php 
										if($companywide_reports[$x]['B']!="")
										{
											echo $companywide_reports[$x]['B']." = "; 
											echo round($companywide_reports[$x]['B'] * 100 / $numofresponses[$x])."%";
										}
									?>
								</td>
								<td style="border: solid 1px">
									<?php 
										if($companywide_reports[$x]['C']!="")
										{
											echo $companywide_reports[$x]['C']." = "; 
											echo round($companywide_reports[$x]['C'] * 100 / $numofresponses[$x])."%";
										}
									?>
								</td>
								<td style="border: solid 1px">
									<?php 
										if($companywide_reports[$x]['D']!="")
										{
											echo $companywide_reports[$x]['D']." = "; 
											echo round($companywide_reports[$x]['D'] * 100 / $numofresponses[$x])."%";
										}
									?>
								</td>
								<td style="border: solid 1px">
									<?php 
										if($companywide_reports[$x]['E']!="")
										{
											echo $companywide_reports[$x]['E']." = "; 
											echo round($companywide_reports[$x]['E'] * 100 / $numofresponses[$x])."%";
										}
									?>
								</td>
								<td style="border: solid 1px">
									<?php echo $numofresponses[$x]; ?>
								</td>
								<td width="20%" style="border: solid 1px">
								<!-- PHP $sendme="showboxy('". $companywide_reports[$x]['comm'] ."');"; -->
								<!-- 	<a onClick="<?= $sendme ?>">A</a> -->
									<?php echo $companywide_reports[$x]['comm']; ?>
								</td>
							</tr>
						<!-- 	<tr>
								<td width="29%" style="text-align:left">2. I feel Greater Sense of Pride for Pioneer</td>
		 							<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q2">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q2">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q2">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q2">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q2">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">3. I feel comfortable and relaxed when I wear them</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q3">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q3">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q3">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q3">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="q3">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">4. I feel happy and confident about myself</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">5. I feel very professional when i'm with clients</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">6. I feel more cautious about how i carry myself because I carry the name Pioneer</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">7. I feel inspired at work</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr><td style="text-align:left;font-style:italic">When I wear the new set of corporate attire...</td></tr>
							<tr>
								<td width="35%" style="text-align:left">8. I feel Excited and Energetic</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">9. I feel Greater Sense of Pride for Pioneer</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">10. I feel comfortable and relaxed when I wear them</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">11. I feel happy and confident about myself</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">12. I feel very professional when i'm with clients</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">13. I feel more cautious about how i carry myself because I carry the name Pioneer</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr>
							<tr>
								<td width="29%" style="text-align:left">14. I feel inspired at work</td>
								<td></td>
								<td></td>
								<td style="border: solid 1px;padding-left:20px;padding-right:20px;">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td style="border: solid 1px">
									<input id="status-question" class="input-radio" type="radio" value="question" name="status[]">
								</td>
								<td width="20%" style="border: solid 1px">
									<input id="status-question" class="input-text" type="text">
								</td>
							</tr> -->
<?php
		$txtctr++;
	}
?>
</table>
