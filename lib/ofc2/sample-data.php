<?php

$title = new title( date("D M d Y") );

$data = array(9,8,7,6,null,4,3,2,1);
$data[4] = new bar_value(5);
$data[4]->set_colour( '#ff0000' );
$data[4]->set_tooltip( 'Hello<br>#val#' );

$bar = new bar_glass();
$bar->set_values( $data );

$chart = new open_flash_chart();
$chart->set_title( $title );
$chart->add_element( $bar );

echo $chart->toString();

?>