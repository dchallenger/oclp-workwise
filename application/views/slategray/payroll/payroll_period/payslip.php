<html>
	<head>
	    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	    <style>
			html, body, div, span, applet, object, iframe,
			h1, h2, h3, h4, h5, h6, p, blockquote, pre,
			a, abbr, acronym, address, big, cite, code,
			del, dfn, em, img, ins, kbd, q, s, samp,
			small, strike, strong, sub, sup, tt, var,
			b, u, i, center,
			dl, dt, dd, ol, ul, li,
			fieldset, form, label, legend,
			table, caption, tbody, tfoot, thead, tr, th, td,
			article, aside, canvas, details, embed, 
			figure, figcaption, footer, header, hgroup, 
			menu, nav, output, ruby, section, summary,
			time, mark, audio, video {
				margin: 0;
				padding: 0;
				border: 0;
				font-size: 100%;
				font: inherit;
				vertical-align: baseline;
			}
			article, aside, details, figcaption, figure, 
			footer, header, hgroup, menu, nav, section {
				display: block;
			}
			body {
				line-height: 1;
			}
			ol, ul {
				list-style: none;
			}
			blockquote, q {
				quotes: none;
			}
			blockquote:before, blockquote:after,
			q:before, q:after {
				content: '';
				content: none;
			}
			table {
				border-collapse: collapse;
				border-spacing: 0;
			}

			.wrapper{
				width: 100%;
				padding: .25in;
				height: 5in;
				font:8pt helvetica,sans-serif;
			}

			td.label{
				width: 17%;
				text-align: right;
			}

			td.value{
				width: 33%;
				font:bold 8pt helvetica, sans-serif;
			}

			.clear{
				clear: both;
			}

			table{
				width: 100%;
			}

			table tr td{
				padding: 5px;
			}

			table.with-border{
				border-collapse:collapse;
			}
			table.with-border,table.with-border th, table.with-border td{
				border: 1px solid black;
			}

			div.column{
				float:left;
				width: 580px;
				height: 550px;
				padding: 10px;
				border: 1px solid black;
			}

			div.footer{
				padding: 5px;
				border-bottom: 3px solid black;
			}

			div.transactions_list{
				height: 460px;	
			}

			td.transactions{
				text-align: right;
				font:bold 8pt helvetica, sans-serif;	
			}

			span.netpay{
				font:bold 9pt helvetica, sans-serif;	
			}
	</style>
</head>
	<body>
		<div class="wrapper">
			<table class="with-border">
				<tr>
					<td class="label">Employee No:</td>
					<td class="value"><?php echo $employee->id_number?></td>
					<td class="label">Company:</td>
					<td class="value"><?php echo $employee->company?></td>
				</tr>
				<tr>
					<td class="label">Employee Name:</td>
					<td class="value"><?php echo $employee->lastname.', '.$employee->firstname?></td>
					<td class="label">Department:</td>
					<td class="value"><?php echo $employee->department?></td>
				</tr>
				<tr>
					<td class="label">Position:</td>
					<td class="value"><?php echo $employee->position?></td>
					<td class="label">Period:</td>
					<td class="value"><?php echo date( 'd-M-Y', strtotime($period->date_from))?> to <?php echo date( 'd-M-Y', strtotime($period->date_to))?></td>
				</tr>
				<tr>
					<td class="label">TIN No:</td>
					<td class="value"><?php echo $employee->tin?></td>
					<td class="label">Payroll Date:</td>
					<td class="value"><?php echo date( 'd-M-Y', strtotime($period->payroll_date))?></td>
				</tr>
			</table>
			<div style="height: 50px;">&nbsp;</div>
			<div class="column">
				<h1>Earnings</h1>
				<hr/>
				<div class="transactions_list"> <?php 
					$gross = 0;
					if(isset($earning)){ ?>
						<table> <?php
							foreach($earning as $transaction){ 
								$gross += $transaction['amount'];
								if( !empty($transaction['record_from']) )

								?>
								<tr>
									<td><?php echo $transaction['transaction_label']?></td>
									<td class="transactions"><?php echo number_format($transaction['amount'], 2, '.', ',')?></td>
								</tr> <?php
							} ?>
						</table>
						<?php	
					} ?>	
				</div>
				<div>
					Gross Earning: <?php echo number_format($gross , 2, '.', ',')?><br/>
					Net Pay: <span class="netpay"><?php echo number_format($netpay['amount'] , 2, '.', ',')?></span>
				</div>
			</div>
			<div class="column" style="float: right">
				<h1>Deductions</h1>
				<hr/>
				<div class="transactions_list"> <?php
					$total_deductions = 0;
					if(isset($deduction)){ ?>
						<table> <?php
							foreach($deduction as $transaction){ 
								$total_deductions += $transaction['amount']?>
								<tr>
									<td><?php echo $transaction['transaction_label']?></td>
									<td class="transactions"><?php echo number_format($transaction['amount'], 2, '.', ',')?></td>
								</tr> <?php
							} ?>
						</table> <?php	
					} ?>	
				</div>
				<div>
					Gross Decutions: <?php echo number_format($total_deductions , 2, '.', ',')?>
				</div>
			</div>
		</div>


		<div class="wrapper">
			<table class="with-border">
				<tr>
					<td class="label">Employee No:</td>
					<td class="value"><?php echo $employee->id_number?></td>
					<td class="label">Company:</td>
					<td class="value"><?php echo $employee->company?></td>
				</tr>
				<tr>
					<td class="label">Employee Name:</td>
					<td class="value"><?php echo $employee->lastname.', '.$employee->firstname?></td>
					<td class="label">Department:</td>
					<td class="value"><?php echo $employee->department?></td>
				</tr>
				<tr>
					<td class="label">Position:</td>
					<td class="value"><?php echo $employee->position?></td>
					<td class="label">Period:</td>
					<td class="value"><?php echo date( 'd-M-Y', strtotime($period->date_from))?> to <?php echo date( 'd-M-Y', strtotime($period->date_to))?></td>
				</tr>
				<tr>
					<td class="label">TIN No:</td>
					<td class="value"><?php echo $employee->tin?></td>
					<td class="label">Payroll Date:</td>
					<td class="value"><?php echo date( 'd-M-Y', strtotime($period->payroll_date))?></td>
				</tr>
			</table>
			<div style="height: 50px;">&nbsp;</div>
			<div class="column">
				<h1>Earnings</h1>
				<hr/>
				<div class="transactions_list"> <?php 
					$gross = 0;
					if(isset($earning)){ ?>
						<table> <?php
							foreach($earning as $transaction){ 
								$gross += $transaction['amount'];?>
								<tr>
									<td><?php echo $transaction['transaction_label']?></td>
									<td class="transactions"><?php echo number_format($transaction['amount'], 2, '.', ',')?></td>
								</tr> <?php
							} ?>
						</table>
						<?php	
					} ?>	
				</div>
				<div>
					Gross Earnings: <?php echo number_format($gross , 2, '.', ',')?><br/>
					Net Pay: <span class="netpay"><?php echo number_format($netpay['amount'] , 2, '.', ',')?></span>
				</div>
			</div>
			<div class="column"  style="float: right">
				<h1>Deductions</h1>
				<hr/>
				<div class="transactions_list"> <?php
					$total_deductions = 0;
					if(isset($deduction)){ ?>
						<table> <?php
							foreach($deduction as $transaction){ 
								$total_deductions += $transaction['amount']?>
								<tr>
									<td><?php echo $transaction['transaction_label']?></td>
									<td class="transactions"><?php echo number_format($transaction['amount'], 2, '.', ',')?></td>
								</tr> <?php
							} ?>
						</table>
						<?php	
					} ?>	
				</div>
				<div>
					Gross Deductions: <?php echo number_format($total_deductions , 2, '.', ',')?>
				</div>
			</div>
		</div>
	</body>
</html>