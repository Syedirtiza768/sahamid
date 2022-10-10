<?php
require_once './config.php';

$sheet_type=$_POST['sheet_type'];
$pannel_costing_id=$_POST['pannel_costing_id'];

$pannel_size_h=$_POST['pannel_size_h'];
$pannel_size_w=$_POST['pannel_size_w'];
$pannel_size_d=$_POST['pannel_size_d'];

$front_door_sf=$_POST['front_door_sf'];
$front_door_ws=$_POST['front_door_ws'];
if(!empty($front_door_sf)){}else{$front_door_sf="0";}
if(!empty($front_door_ws)){}else{$front_door_ws="0";}

$s_front_door_sf=$_POST['s_front_door_sf'];
$s_front_door_ws=$_POST['s_front_door_ws'];
if(!empty($s_front_door_sf)){}else{$s_front_door_sf="0";}
if(!empty($s_front_door_ws)){}else{$s_front_door_ws="0";}

$t_front_door_sf=$_POST['t_front_door_sf'];
$t_front_door_ws=$_POST['t_front_door_ws'];
if(!empty($t_front_door_sf)){}else{$t_front_door_sf="0";}
if(!empty($t_front_door_ws)){}else{$t_front_door_ws="0";}

$f_front_door_sf=$_POST['f_front_door_sf'];
$f_front_door_ws=$_POST['f_front_door_ws'];
if(!empty($f_front_door_sf)){}else{$f_front_door_sf="0";}
if(!empty($f_front_door_ws)){}else{$f_front_door_ws="0";}

$fth_front_door_sf=$_POST['fth_front_door_sf'];
$fth_front_door_ws=$_POST['fth_front_door_ws'];
if(!empty($fth_front_door_sf)){}else{$fth_front_door_sf="0";}
if(!empty($fth_front_door_ws)){}else{$fth_front_door_ws="0";}

$six_front_door_sf=$_POST['six_front_door_sf'];
$six_front_door_ws=$_POST['six_front_door_ws'];
if(!empty($six_front_door_sf)){}else{$six_front_door_sf="0";}
if(!empty($six_front_door_ws)){}else{$six_front_door_ws="0";}

$sth_front_door_sf=$_POST['sth_front_door_sf'];
$sth_front_door_ws=$_POST['sth_front_door_ws'];
if(!empty($sth_front_door_sf)){}else{$sth_front_door_sf="0";}
if(!empty($sth_front_door_ws)){}else{$sth_front_door_ws="0";}

$e_front_door_sf=$_POST['e_front_door_sf'];
$e_front_door_ws=$_POST['e_front_door_ws'];
if(!empty($e_front_door_sf)){}else{$e_front_door_sf="0";}
if(!empty($e_front_door_ws)){}else{$e_front_door_ws="0";}

$n_front_door_sf=$_POST['n_front_door_sf'];
$n_front_door_ws=$_POST['n_front_door_ws'];
if(!empty($n_front_door_sf)){}else{$n_front_door_sf="0";}
if(!empty($n_front_door_ws)){}else{$n_front_door_ws="0";}

$back_door_sf=$_POST['back_door_sf'];
$back_door_ws=$_POST['back_door_ws'];
if(!empty($back_door_sf)){}else{$back_door_sf="0";}
if(!empty($back_door_ws)){}else{$back_door_ws="0";}

$s_back_door_sf=$_POST['s_back_door_sf'];
$s_back_door_ws=$_POST['s_back_door_ws'];
if(!empty($s_back_door_sf)){}else{$s_back_door_sf="0";}
if(!empty($s_back_door_ws)){}else{$s_back_door_ws="0";}

$t_back_door_sf=$_POST['t_back_door_sf'];
$t_back_door_ws=$_POST['t_back_door_ws'];
if(!empty($t_back_door_sf)){}else{$t_back_door_sf="0";}
if(!empty($t_back_door_ws)){}else{$t_back_door_ws="0";}

$f_back_door_sf=$_POST['f_back_door_sf'];
$f_back_door_ws=$_POST['f_back_door_ws'];
if(!empty($f_back_door_sf)){}else{$f_back_door_sf="0";}
if(!empty($f_back_door_ws)){}else{$f_back_door_ws="0";}

$fth_back_door_sf=$_POST['fth_back_door_sf'];
$fth_back_door_ws=$_POST['fth_back_door_ws'];
if(!empty($fth_back_door_sf)){}else{$fth_back_door_sf="0";}
if(!empty($fth_back_door_ws)){}else{$fth_back_door_ws="0";}

$six_back_door_sf=$_POST['six_back_door_sf'];
$six_back_door_ws=$_POST['six_back_door_ws'];
if(!empty($six_back_door_sf)){}else{$six_back_door_sf="0";}
if(!empty($six_back_door_ws)){}else{$six_back_door_ws="0";}

$sth_back_door_sf=$_POST['sth_back_door_sf'];
$sth_back_door_ws=$_POST['sth_back_door_ws'];
if(!empty($sth_back_door_sf)){}else{$sth_back_door_sf="0";}
if(!empty($sth_back_door_ws)){}else{$sth_back_door_ws="0";}

$e_back_door_sf=$_POST['e_back_door_sf'];
$e_back_door_ws=$_POST['e_back_door_ws'];
if(!empty($e_back_door_sf)){}else{$e_back_door_sf="0";}
if(!empty($e_back_door_ws)){}else{$e_back_door_ws="0";}

$n_back_door_sf=$_POST['n_back_door_sf'];
$n_back_door_ws=$_POST['n_back_door_ws'];
if(!empty($n_back_door_sf)){}else{$n_back_door_sf="0";}
if(!empty($n_back_door_ws)){}else{$n_back_door_ws="0";}

$side_door_RL_sf=$_POST['side_door_RL_sf'];
$side_door_RL_ws=$_POST['side_door_RL_ws'];
if(!empty($side_door_RL_sf)){}else{$side_door_RL_sf="0";}
if(!empty($side_door_RL_ws)){}else{$side_door_RL_ws="0";}

$VS_piece_FB_sf=$_POST['VS_piece_FB_sf'];
$VS_piece_FB_ws=$_POST['VS_piece_FB_ws'];
if(!empty($VS_piece_FB_sf)){}else{$VS_piece_FB_sf="0";}
if(!empty($VS_piece_FB_ws)){}else{$VS_piece_FB_ws="0";}


$HS_piece_FB_sf=$_POST['HS_piece_FB_sf'];
$HS_piece_FB_ws=$_POST['HS_piece_FB_ws'];
if(!empty($HS_piece_FB_sf)){}else{$HS_piece_FB_sf="0";}
if(!empty($HS_piece_FB_ws)){}else{$HS_piece_FB_ws="0";}

$HS_piece_RL_sf=$_POST['HS_piece_RL_sf'];
$HS_piece_RL_ws=$_POST['HS_piece_RL_ws'];
if(!empty($HS_piece_RL_sf)){}else{$HS_piece_RL_sf="0";}
if(!empty($HS_piece_RL_ws)){}else{$HS_piece_RL_ws="0";}

$componnent_plate_sf=$_POST['componnent_plate_sf'];
$componnent_plate_ws=$_POST['componnent_plate_ws'];
if(!empty($componnent_plate_sf)){}else{$componnent_plate_sf="0";}
if(!empty($componnent_plate_ws)){}else{$componnent_plate_ws="0";}

$s_componnent_plate_sf=$_POST['s_componnent_plate_sf'];
$s_componnent_plate_ws=$_POST['s_componnent_plate_ws'];
if(!empty($s_componnent_plate_sf)){}else{$s_componnent_plate_sf="0";}
if(!empty($s_componnent_plate_ws)){}else{$s_componnent_plate_ws="0";}

$t_componnent_plate_sf=$_POST['t_componnent_plate_sf'];
$t_componnent_plate_ws=$_POST['t_componnent_plate_ws'];
if(!empty($t_componnent_plate_sf)){}else{$t_componnent_plate_sf="0";}
if(!empty($t_componnent_plate_ws)){}else{$t_componnent_plate_ws="0";}

$f_componnent_plate_sf=$_POST['f_componnent_plate_sf'];
$f_componnent_plate_ws=$_POST['f_componnent_plate_ws'];
if(!empty($f_componnent_plate_sf)){}else{$f_componnent_plate_sf="0";}
if(!empty($f_componnent_plate_ws)){}else{$f_componnent_plate_ws="0";}

$fth_componnent_plate_sf=$_POST['fth_componnent_plate_sf'];
$fth_componnent_plate_ws=$_POST['fth_componnent_plate_ws'];
if(!empty($fth_componnent_plate_sf)){}else{$fth_componnent_plate_sf="0";}
if(!empty($fth_componnent_plate_ws)){}else{$fth_componnent_plate_ws="0";}

$six_componnent_plate_sf=$_POST['six_componnent_plate_sf'];
$six_componnent_plate_ws=$_POST['six_componnent_plate_ws'];
if(!empty($six_componnent_plate_sf)){}else{$six_componnent_plate_sf="0";}
if(!empty($six_componnent_plate_ws)){}else{$six_componnent_plate_ws="0";}

$sth_componnent_plate_sf=$_POST['sth_componnent_plate_sf'];
$sth_componnent_plate_ws=$_POST['sth_componnent_plate_ws'];
if(!empty($sth_componnent_plate_sf)){}else{$sth_componnent_plate_sf="0";}
if(!empty($sth_componnent_plate_ws)){}else{$sth_componnent_plate_ws="0";}

$e_componnent_plate_sf=$_POST['e_componnent_plate_sf'];
$e_componnent_plate_ws=$_POST['e_componnent_plate_ws'];
if(!empty($e_componnent_plate_sf)){}else{$e_componnent_plate_sf="0";}
if(!empty($e_componnent_plate_ws)){}else{$e_componnent_plate_ws="0";}

$n_componnent_plate_sf=$_POST['n_componnent_plate_sf'];
$n_componnent_plate_ws=$_POST['n_componnent_plate_ws'];
if(!empty($n_componnent_plate_sf)){}else{$n_componnent_plate_sf="0";}
if(!empty($n_componnent_plate_ws)){}else{$n_componnent_plate_ws="0";}


$pallet_sf=$_POST['pallet_sf'];
$pallet_ws=$_POST['pallet_ws'];
if(!empty($pallet_sf)){}else{$pallet_sf="0";}
if(!empty($pallet_ws)){}else{$pallet_ws="0";}

$s_pallet_sf=$_POST['s_pallet_sf'];
$s_pallet_ws=$_POST['s_pallet_ws'];
if(!empty($s_pallet_sf)){}else{$s_pallet_sf="0";}
if(!empty($s_pallet_ws)){}else{$s_pallet_ws="0";}

$FB_base_sf=$_POST['FB_base_sf'];
$FB_base_ws=$_POST['FB_base_ws'];
if(!empty($FB_base_sf)){}else{$FB_base_sf="0";}
if(!empty($FB_base_ws)){}else{$FB_base_ws="0";}

$RL_base_sf=$_POST['RL_base_sf'];
$RL_base_ws=$_POST['RL_base_ws'];
if(!empty($RL_base_sf)){}else{$RL_base_sf="0";}
if(!empty($RL_base_ws)){}else{$RL_base_ws="0";}

$VI_door_U_sf=$_POST['VI_door_U_sf'];
$VI_door_U_ws=$_POST['VI_door_U_ws'];
if(!empty($VI_door_U_sf)){}else{$VI_door_U_sf="0";}
if(!empty($VI_door_U_ws)){}else{$VI_door_U_ws="0";}

$HI_door_u_sf=$_POST['HI_door_u_sf'];
$HI_door_u_ws=$_POST['HI_door_u_ws'];
if(!empty($HI_door_u_sf)){}else{$HI_door_u_sf="0";}
if(!empty($HI_door_u_ws)){}else{$HI_door_u_ws="0";}

$VI_L_type_sf=$_POST['VI_L_type_sf'];
$VI_L_type_ws=$_POST['VI_L_type_ws'];
if(!empty($VI_L_type_sf)){}else{$VI_L_type_sf="0";}
if(!empty($VI_L_type_ws)){}else{$VI_L_type_ws="0";}

$HI_U_type_sf=$_POST['HI_U_type_sf'];
$HI_U_type_ws=$_POST['HI_U_type_ws'];
if(!empty($HI_U_type_sf)){}else{$HI_U_type_sf="0";}
if(!empty($HI_U_type_ws)){}else{$HI_U_type_ws="0";}

$top_sf=$_POST['top_sf'];
$top_ws=$_POST['top_ws'];
if(!empty($top_sf)){}else{$top_sf="0";}
if(!empty($top_ws)){}else{$top_ws="0";}

$bottom_sf=$_POST['bottom_sf'];
$bottom_ws=$_POST['bottom_ws'];
if(!empty($bottom_sf)){}else{$bottom_sf="0";}
if(!empty($bottom_ws)){}else{$bottom_ws="0";}

$protection_sheet_sf=$_POST['protection_sheet_sf'];
$protection_sheet_ws=$_POST['protection_sheet_ws'];
if(!empty($protection_sheet_sf)){}else{$protection_sheet_sf="0";}
if(!empty($protection_sheet_ws)){}else{$protection_sheet_ws="0";}

$s_protection_sheet_sf=$_POST['s_protection_sheet_sf'];
$s_protection_sheet_ws=$_POST['s_protection_sheet_ws'];
if(!empty($s_protection_sheet_sf)){}else{$s_protection_sheet_sf="0";}
if(!empty($s_protection_sheet_ws)){}else{$s_protection_sheet_ws="0";}

$miscellaneous_sf=$_POST['miscellaneous_sf'];
$miscellaneous_ws=$_POST['miscellaneous_ws'];
if(!empty($miscellaneous_sf)){}else{$miscellaneous_sf="0";}
if(!empty($miscellaneous_ws)){}else{$miscellaneous_ws="0";}

$sheet_type=$_POST['sheet_type'];
$matal_s_price=$_POST['matal_s_price'];
$stainless_s_price=$_POST['stainless_s_price'];
$galvanized_s_price=$_POST['galvanized_s_price'];


$pl_mf = $_POST['pl_mf'];
$pl_model = $_POST['pl_model'];
$pl_qty = $_POST['pl_qty'];
$pl_cost = $_POST['pl_cost'];
if(!empty($pl_mf)){}else{$pl_mf="0";}
if(!empty($pl_model)){}else{$pl_model="0";}
if(!empty($pl_qty)){}else{$pl_qty="0";}
if(!empty($pl_cost)){}else{$pl_cost="0";}

$h_mf = $_POST['h_mf'];
$h_model = $_POST['h_model'];
if(!empty($h_mf)){}else{$h_mf="0";}
if(!empty($h_model)){}else{$h_model="0";}

$h_qty = $_POST['h_qty'];
$h_cost = $_POST['h_cost'];
if(!empty($h_qty)){}else{$h_qty="0";}
if(!empty($h_cost)){}else{$h_cost="0";}

$as_qty = $_POST['as_qty'];
$as_cost = $_POST['as_cost'];
if(!empty($as_qty)){}else{$as_qty="0";}
if(!empty($as_cost)){}else{$as_cost="0";}

$gk_qty = $_POST['gk_qty'];
$gk_cost = $_POST['gk_cost'];
if(!empty($gk_qty)){}else{$gk_qty="0";}
if(!empty($gk_cost)){}else{$gk_cost="0";}

$i_qty = $_POST['i_qty'];
$i_cost = $_POST['i_cost'];
if(!empty($i_qty)){}else{$i_qty="0";}
if(!empty($i_cost)){}else{$i_cost="0";}

$cd_mf = $_POST['cd_mf'];
$cd_model = $_POST['cd_model'];
$cd_qty = $_POST['cd_qty'];
$cd_cost = $_POST['cd_cost'];
if(!empty($cd_mf)){}else{$cd_mf="0";}
if(!empty($cd_model)){}else{$cd_model="0";}
if(!empty($cd_qty)){}else{$cd_qty="0";}
if(!empty($cd_cost)){}else{$cd_cost="0";}

$pc_mf = $_POST['pc_mf'];
$pc_model = $_POST['pc_model'];
$pc_cost = $_POST['pc_cost'];
if(!empty($pc_mf)){}else{$pc_mf="0";}
if(!empty($pc_model)){}else{$pc_model="0";}
if(!empty($pc_cost)){}else{$pc_cost="0";}

$t_5_foot_size = $_POST['t_5_foot_size'];
$t_5_sleeve_cost = $_POST['t_5_sleeve_cost'];
$t_5_factor = $_POST['t_5_factor'];
$t_5_sleeve_total_cost = $_POST['t_5_sleeve_total_cost'];
$t_5_bbr_weight = $_POST['t_5_bbr_weight'];
if(!empty($t_5_foot_size)){}else{$t_5_foot_size="0";}
if(!empty($t_5_sleeve_cost)){}else{$t_5_sleeve_cost="0";}
if(!empty($t_5_factor)){}else{$t_5_factor="0";}
if(!empty($t_5_sleeve_total_cost)){}else{$t_5_sleeve_total_cost="0";}
if(!empty($t_5_bbr_weight)){}else{$t_5_bbr_weight="0";}

$tf_5_foot_size = $_POST['tf_5_foot_size'];
$tf_5_sleeve_cost = $_POST['tf_5_sleeve_cost'];
$tf_5_factor = $_POST['tf_5_factor'];
$tf_5_sleeve_total_cost = $_POST['tf_5_sleeve_total_cost'];
$tf_5_bbr_weight = $_POST['tf_5_bbr_weight'];
if(!empty($tf_5_foot_size)){}else{$tf_5_foot_size="0";}
if(!empty($tf_5_sleeve_cost)){}else{$tf_5_sleeve_cost="0";}
if(!empty($tf_5_factor)){}else{$tf_5_factor="0";}
if(!empty($tf_5_sleeve_total_cost)){}else{$tf_5_sleeve_total_cost="0";}
if(!empty($tf_5_bbr_weight)){}else{$tf_5_bbr_weight="0";}

$tf_10_foot_size = $_POST['tf_10_foot_size'];
$tf_10_sleeve_cost = $_POST['tf_10_sleeve_cost'];
$tf_10_factor = $_POST['tf_10_factor'];
$tf_10_sleeve_total_cost = $_POST['tf_10_sleeve_total_cost'];
$tf_10_bbr_weight = $_POST['tf_10_bbr_weight'];
if(!empty($tf_10_foot_size)){}else{$tf_10_foot_size="0";}
if(!empty($tf_10_sleeve_cost)){}else{$tf_10_sleeve_cost="0";}
if(!empty($tf_10_factor)){}else{$tf_10_factor="0";}
if(!empty($tf_10_sleeve_total_cost)){}else{$tf_10_sleeve_total_cost="0";}
if(!empty($tf_10_bbr_weight)){}else{$tf_10_bbr_weight="0";}

$ty_5_foot_size = $_POST['ty_5_foot_size'];
$ty_5_sleeve_cost = $_POST['ty_5_sleeve_cost'];
$ty_5_factor = $_POST['ty_5_factor'];
$ty_5_sleeve_total_cost= $_POST['ty_5_sleeve_total_cost'];
$ty_5_bbr_weight = $_POST['ty_5_bbr_weight'];
if(!empty($ty_5_foot_size)){}else{$ty_5_foot_size="0";}
if(!empty($ty_5_sleeve_cost)){}else{$ty_5_sleeve_cost="0";}
if(!empty($ty_5_factor)){}else{$ty_5_factor="0";}
if(!empty($ty_5_sleeve_total_cost)){}else{$ty_5_sleeve_total_cost="0";}
if(!empty($ty_5_bbr_weight)){}else{$ty_5_bbr_weight="0";}

$ty_10_foot_size = $_POST['ty_10_foot_size'];
$ty_10_sleeve_cost = $_POST['ty_10_sleeve_cost'];
$ty_10_factor = $_POST['ty_10_factor'];
$ty_10_sleeve_total_cost = $_POST['ty_10_sleeve_total_cost'];
$ty_10_bbr_weight = $_POST['ty_10_bbr_weight'];
if(!empty($ty_10_foot_size)){}else{$ty_10_foot_size="0";}
if(!empty($ty_10_sleeve_cost)){}else{$ty_10_sleeve_cost="0";}
if(!empty($ty_10_factor)){}else{$ty_10_factor="0";}
if(!empty($ty_10_sleeve_total_cost)){}else{$ty_10_sleeve_total_cost="0";}
if(!empty($ty_10_bbr_weight)){}else{$ty_10_bbr_weight="0";}

$f_5_foot_size = $_POST['f_5_foot_size'];
$f_5_sleeve_cost = $_POST['f_5_sleeve_cost'];
$f_5_factor = $_POST['f_5_factor'];
$f_5_sleeve_total_cost = $_POST['f_5_sleeve_total_cost'];
$f_5_bbr_weight = $_POST['f_5_bbr_weight'];
if(!empty($f_5_foot_size)){}else{$f_5_foot_size="0";}
if(!empty($f_5_sleeve_cost)){}else{$f_5_sleeve_cost="0";}
if(!empty($f_5_factor)){}else{$f_5_factor="0";}
if(!empty($f_5_sleeve_total_cost)){}else{$f_5_sleeve_total_cost="0";}
if(!empty($f_5_bbr_weight)){}else{$f_5_bbr_weight="0";}

$f_10_foot_size = $_POST['f_10_foot_size'];
$f_10_sleeve_cost = $_POST['f_10_sleeve_cost'];
$f_10_factor = $_POST['f_10_factor'];
$f_10_sleeve_total_cost = $_POST['f_10_sleeve_total_cost'];
$f_10_bbr_weight = $_POST['f_10_bbr_weight'];
if(!empty($f_10_foot_size)){}else{$f_10_foot_size="0";}
if(!empty($f_10_sleeve_cost)){}else{$f_10_sleeve_cost="0";}
if(!empty($f_10_factor)){}else{$f_10_factor="0";}
if(!empty($f_10_sleeve_total_cost)){}else{$f_10_sleeve_total_cost="0";}
if(!empty($f_10_bbr_weight)){}else{$f_10_bbr_weight="0";}

$fy_5_foot_size = $_POST['fy_5_foot_size'];
$fy_5_sleeve_cost = $_POST['fy_5_sleeve_cost'];
$fy_5_factor = $_POST['fy_5_factor'];
$fy_5_sleeve_total_cost = $_POST['fy_5_sleeve_total_cost'];
$fy_5_bbr_weight = $_POST['fy_5_bbr_weight'];
if(!empty($fy_5_foot_size)){}else{$fy_5_foot_size="0";}
if(!empty($fy_5_sleeve_cost)){}else{$fy_5_sleeve_cost="0";}
if(!empty($fy_5_factor)){}else{$fy_5_factor="0";}
if(!empty($fy_5_sleeve_total_cost)){}else{$fy_5_sleeve_total_cost="0";}
if(!empty($fy_5_bbr_weight)){}else{$fy_5_bbr_weight="0";}

$fy_10_foot_size = $_POST['fy_10_foot_size'];
$fy_10_sleeve_cost = $_POST['fy_10_sleeve_cost'];
$fy_10_factor = $_POST['fy_10_factor'];
$fy_10_sleeve_total_cost = $_POST['fy_10_sleeve_total_cost'];
$fy_10_bbr_weight = $_POST['fy_10_bbr_weight'];
if(!empty($fy_10_foot_size)){}else{$fy_10_foot_size="0";}
if(!empty($fy_10_sleeve_cost)){}else{$fy_10_sleeve_cost="0";}
if(!empty($fy_10_factor)){}else{$fy_10_factor="0";}
if(!empty($fy_10_sleeve_total_cost)){}else{$fy_10_sleeve_total_cost="0";}
if(!empty($fy_10_bbr_weight)){}else{$fy_10_bbr_weight="0";}

$s_5_foot_size = $_POST['s_5_foot_size'];
$s_5_sleeve_cost = $_POST['s_5_sleeve_cost'];
$s_5_factor = $_POST['s_5_factor'];
$s_5_sleeve_total_cost = $_POST['s_5_sleeve_total_cost'];
$s_5_bbr_weight = $_POST['s_5_bbr_weight'];
if(!empty($s_5_foot_size)){}else{$s_5_foot_size="0";}
if(!empty($s_5_sleeve_cost)){}else{$s_5_sleeve_cost="0";}
if(!empty($s_5_factor)){}else{$s_5_factor="0";}
if(!empty($s_5_sleeve_total_cost)){}else{$s_5_sleeve_total_cost="0";}
if(!empty($s_5_bbr_weight)){}else{$s_5_bbr_weight="0";}

$s_10_foot_size = $_POST['s_10_foot_size'];
$s_10_sleeve_cost = $_POST['s_10_sleeve_cost'];
$s_10_factor = $_POST['s_10_factor'];
$s_10_sleeve_total_cost = $_POST['s_10_sleeve_total_cost'];
$s_10_bbr_weight = $_POST['s_10_bbr_weight'];
if(!empty($s_10_foot_size)){}else{$s_10_foot_size="0";}
if(!empty($s_10_sleeve_cost)){}else{$s_10_sleeve_cost="0";}
if(!empty($s_10_factor)){}else{$s_10_factor="0";}
if(!empty($s_10_sleeve_total_cost)){}else{$s_10_sleeve_total_cost="0";}
if(!empty($s_10_bbr_weight)){}else{$s_10_bbr_weight="0";}

$e_5_foot_size = $_POST['e_5_foot_size'];
$e_5_sleeve_cost = $_POST['e_5_sleeve_cost'];
$e_5_factor = $_POST['e_5_factor'];
$e_5_sleeve_total_cost = $_POST['e_5_sleeve_total_cost'];
$e_5_bbr_weight = $_POST['e_5_bbr_weight'];
if(!empty($e_5_foot_size)){}else{$e_5_foot_size="0";}
if(!empty($e_5_sleeve_cost)){}else{$e_5_sleeve_cost="0";}
if(!empty($e_5_factor)){}else{$e_5_factor="0";}
if(!empty($e_5_sleeve_total_cost)){}else{$e_5_sleeve_total_cost="0";}
if(!empty($e_5_bbr_weight)){}else{$e_5_bbr_weight="0";}

$e_10_foot_size = $_POST['e_10_foot_size'];
$e_10_sleeve_cost = $_POST['e_10_sleeve_cost'];
$e_10_factor = $_POST['e_10_factor'];
$e_10_sleeve_total_cost = $_POST['e_10_sleeve_total_cost'];
$e_10_bbr_weight = $_POST['e_10_bbr_weight'];
if(!empty($e_10_foot_size)){}else{$e_10_foot_size="0";}
if(!empty($e_10_sleeve_cost)){}else{$e_10_sleeve_cost="0";}
if(!empty($e_10_factor)){}else{$e_10_factor="0";}
if(!empty($e_10_sleeve_total_cost)){}else{$e_10_sleeve_total_cost="0";}
if(!empty($e_10_bbr_weight)){}else{$e_10_bbr_weight="0";}

$h_5_foot_size = $_POST['h_5_foot_size'];
$h_5_sleeve_cost = $_POST['h_5_sleeve_cost'];
$h_5_factor = $_POST['h_5_factor'];
$h_5_sleeve_total_cost = $_POST['h_5_sleeve_total_cost'];
$h_5_bbr_weight = $_POST['h_5_bbr_weight'];
if(!empty($h_5_foot_size)){}else{$h_5_foot_size="0";}
if(!empty($h_5_sleeve_cost)){}else{$h_5_sleeve_cost="0";}
if(!empty($h_5_factor)){}else{$h_5_factor="0";}
if(!empty($h_5_sleeve_total_cost)){}else{$h_5_sleeve_total_cost="0";}
if(!empty($h_5_bbr_weight)){}else{$h_5_bbr_weight="0";}

$h_10_foot_size = $_POST['h_10_foot_size'];
$h_10_sleeve_cost = $_POST['h_10_sleeve_cost'];
$h_10_factor = $_POST['h_10_factor'];
$h_10_sleeve_total_cost = $_POST['h_10_sleeve_total_cost'];
$h_10_bbr_weight = $_POST['h_10_bbr_weight'];
if(!empty($h_10_foot_size)){}else{$h_10_foot_size="0";}
if(!empty($h_10_sleeve_cost)){}else{$h_10_sleeve_cost="0";}
if(!empty($h_10_factor)){}else{$h_10_factor="0";}
if(!empty($h_10_sleeve_total_cost)){}else{$h_10_sleeve_total_cost="0";}
if(!empty($h_10_bbr_weight)){}else{$h_10_bbr_weight="0";}

$ot_5_foot_size = $_POST['ot_5_foot_size'];
$ot_5_sleeve_cost = $_POST['ot_5_sleeve_cost'];
$ot_5_factor = $_POST['ot_5_factor'];
$ot_5_sleeve_total_cost = $_POST['ot_5_sleeve_total_cost'];
$ot_5_bbr_weight = $_POST['ot_5_bbr_weight'];
if(!empty($ot_5_foot_size)){}else{$ot_5_foot_size="0";}
if(!empty($ot_5_sleeve_cost)){}else{$ot_5_sleeve_cost="0";}
if(!empty($ot_5_factor)){}else{$ot_5_factor="0";}
if(!empty($ot_5_sleeve_total_cost)){}else{$ot_5_sleeve_total_cost="0";}
if(!empty($ot_5_bbr_weight)){}else{$ot_5_bbr_weight="0";}

$ot_10_foot_size = $_POST['ot_10_foot_size'];
$ot_10_sleeve_cost = $_POST['ot_10_sleeve_cost'];
$ot_10_factor = $_POST['ot_10_factor'];
$ot_10_sleeve_total_cost = $_POST['ot_10_sleeve_total_cost'];
$ot_10_bbr_weight = $_POST['ot_10_bbr_weight'];
if(!empty($ot_10_foot_size)){}else{$ot_10_foot_size="0";}
if(!empty($ot_10_sleeve_cost)){}else{$ot_10_sleeve_cost="0";}
if(!empty($ot_10_factor)){}else{$ot_10_factor="0";}
if(!empty($ot_10_sleeve_total_cost)){}else{$ot_10_sleeve_total_cost="0";}
if(!empty($ot_10_bbr_weight)){}else{$ot_10_bbr_weight="0";}

$of_10_foot_size = $_POST['of_10_foot_size'];
$of_10_sleeve_cost = $_POST['of_10_sleeve_cost'];
$of_10_factor = $_POST['of_10_factor'];
$of_10_sleeve_total_cost = $_POST['of_10_sleeve_total_cost'];
$of_10_bbr_weight = $_POST['of_10_bbr_weight'];
if(!empty($of_10_foot_size)){}else{$of_10_foot_size="0";}
if(!empty($of_10_sleeve_cost)){}else{$of_10_sleeve_cost="0";}
if(!empty($of_10_factor)){}else{$of_10_factor="0";}
if(!empty($of_10_sleeve_total_cost)){}else{$of_10_sleeve_total_cost="0";}
if(!empty($of_10_bbr_weight)){}else{$of_10_bbr_weight="0";}

$bus_bar_price = $_POST['bus_bar_price'];
if(!empty($bus_bar_price)){}else{$bus_bar_price="0";}


$gauges_total_weight  =  $front_door_ws+$t_front_door_ws+$s_front_door_ws+$f_front_door_ws+$fth_front_door_ws+$six_front_door_ws+
$sth_front_door_ws+$e_front_door_ws+$n_front_door_ws+
$back_door_ws+$s_back_door_ws+$t_back_door_ws+$f_back_door_ws+$fth_back_door_ws+$six_back_door_ws+$sth_back_door_ws+$e_back_door_ws+
$n_back_door_ws+$side_door_RL_ws+$VS_piece_FB_ws+$HS_piece_FB_ws+$HS_piece_RL_ws+
$componnent_plate_ws+$s_componnent_plate_ws+$t_componnent_plate_ws+$f_componnent_plate_ws+$fth_componnent_plate_ws+$six_componnent_plate_ws+
$sth_componnent_plate_ws+$e_componnent_plate_ws+$n_componnent_plate_ws+
$pallet_ws+$s_pallet_ws+
$FB_base_ws+$RL_base_ws+$VI_door_U_ws+$HI_door_u_ws+$VI_L_type_ws+$HI_U_type_ws+$top_ws+
$bottom_ws+$protection_sheet_ws+$s_protection_sheet_ws+$miscellaneous_ws;

$total_square_feet  =  $front_door_sf+$t_front_door_sf+$s_front_door_sf+$f_front_door_sf+$fth_front_door_sf+$six_front_door_sf+
$sth_front_door_sf+$e_front_door_sf+$n_front_door_sf+
$back_door_sf+$t_back_door_sf+$s_back_door_sf+$f_back_door_sf+$fth_back_door_sf+$six_back_door_sf+$sth_back_door_sf+$e_back_door_sf+
$n_back_door_sf+
$side_door_RL_sf+$VS_piece_FB_sf+$HS_piece_FB_sf+$HS_piece_RL_sf+
$componnent_plate_sf+$s_componnent_plate_sf+$t_componnent_plate_sf+$f_componnent_plate_sf+$fth_componnent_plate_sf+$six_componnent_plate_sf+
$sth_componnent_plate_sf+$e_componnent_plate_sf+$n_componnent_plate_sf+
$pallet_sf+$s_pallet_sf+
$FB_base_sf+$RL_base_sf+$VI_door_U_sf+$HI_door_u_sf+$VI_L_type_sf+$HI_U_type_sf
+$top_sf+$bottom_sf+$protection_sheet_sf+$s_protection_sheet_sf+$miscellaneous_sf;

$s_by_sf = $total_square_feet/32;

$sheet_consume = $s_by_sf;

if($sheet_type == "0"){
    $mlt_gauge_pr = $gauges_total_weight * $matal_s_price ;
    $twelve_SWG = 62*$sheet_consume* $matal_s_price;
    $f_SWG=48.64*$sheet_consume*$matal_s_price;
    $sixtn_SWG = 38.4*$sheet_consume*$matal_s_price;
    $atn_SMG = 28*$sheet_consume*$matal_s_price;
    $twenty_SMG = 23*$sheet_consume*$matal_s_price;
    

}
else if($sheet_type == "1"){
    $mlt_gauge_pr = $gauges_total_weight*$stainless_s_price ;
    $twelve_SWG = 62*$sheet_consume*$stainless_s_price;
    $f_SWG = 48.64*$sheet_consume*$stainless_s_price;
    $sixtn_SWG = 38.4*$sheet_consume*$stainless_s_price;
    $atn_SMG = 28*$sheet_consume*$stainless_s_price;
    $twenty_SMG = 23*$sheet_consume*$stainless_s_price;
}
else{
    $mlt_gauge_pr = $gauges_total_weight*$galvanized_s_price ;
    $twelve_SWG = 62*$sheet_consume*$galvanized_s_price;
    $f_SWG=48.64*$sheet_consume*$galvanized_s_price;
    $sixtn_SWG = 38.4*$sheet_consume*$galvanized_s_price;
    $atn_SMG = 28*$sheet_consume*$galvanized_s_price;
    $twenty_SMG = 23*$sheet_consume*$galvanized_s_price;

}
$bbr_total_weight=$t_5_bbr_weight+$tf_5_bbr_weight+$tf_10_bbr_weight+$ty_5_bbr_weight+$ty_10_bbr_weight+$f_5_bbr_weight+$f_10_bbr_weight+
$fy_5_bbr_weight+$fy_10_bbr_weight+$s_5_bbr_weight+$s_10_bbr_weight+$e_5_bbr_weight+$e_10_bbr_weight+$h_5_bbr_weight+$h_10_bbr_weight+$ot_5_bbr_weight+
$ot_10_bbr_weight+$of_10_bbr_weight;

$bbr_total_cost=$bus_bar_price*$bbr_total_weight;

$tin_cost=90*$bbr_total_weight;

$total_sleeve_cost=$t_5_sleeve_total_cost+$tf_5_sleeve_total_cost+$tf_10_sleeve_total_cost+$ty_5_sleeve_total_cost+$ty_10_sleeve_total_cost+$f_5_sleeve_total_cost+$f_10_sleeve_total_cost+
$fy_5_sleeve_total_cost+$fy_10_sleeve_total_cost+$s_5_sleeve_total_cost+$s_10_sleeve_total_cost+$e_5_sleeve_total_cost+$e_10_sleeve_total_cost+$h_5_sleeve_total_cost+$h_10_sleeve_total_cost+$ot_5_sleeve_total_cost+
$ot_10_sleeve_total_cost+$of_10_sleeve_total_cost;


$sql ="UPDATE pannel_costing SET gauges_total_weight = '".$gauges_total_weight."', total_SF = '".$total_square_feet."',
s_by_sf = '".$s_by_sf."',sheet_consume = '".$sheet_consume."',mult_gauge_price = '".$mlt_gauge_pr."',
 14_SWG_price='".$f_SWG."',16_SWG_price='".$sixtn_SWG."',18_SWG_price='".$atn_SMG."',12_SWG_price='".$twelve_SWG."'
,20_SWG_price='".$twenty_SMG."'   WHERE id = '".$pannel_costing_id."'";
if (mysqli_query($conn, $sql)) {
   

  $sql ="UPDATE bus_bar_sheet SET bus_bar_price = '".$bus_bar_price."',
  20_5_foot_size = '".$t_5_foot_size."',20_5_sleeve_cost = '".$t_5_sleeve_cost."',20_5_factor = '".$t_5_factor."',20_5_sleeve_total_cost = '".$t_5_sleeve_total_cost."',20_5_bbr_weight = '".$t_5_bbr_weight."',
  25_5_foot_size = '".$tf_5_foot_size."',25_5_sleeve_cost = '".$tf_5_sleeve_cost."',25_5_factor='".$tf_5_factor."', 25_5_sleeve_total_cost = '".$tf_5_sleeve_total_cost."',25_5_bbr_weight = '".$tf_5_bbr_weight."',
  25_10_foot_size = '".$tf_10_foot_size."',25_10_sleeve_cost = '".$tf_10_sleeve_cost."',25_10_factor='".$tf_10_factor."', 25_10_sleeve_total_cost = '".$tf_10_sleeve_total_cost."',25_10_bbr_weight = '".$tf_10_bbr_weight."',
  30_5_foot_size = '".$ty_5_foot_size."',30_5_sleeve_cost = '".$ty_5_sleeve_cost."',30_5_factor='".$ty_5_factor."', 30_5_sleeve_total_cost = '".$ty_5_sleeve_total_cost."',30_5_bbr_weight = '".$ty_5_bbr_weight."',
  30_10_foot_size = '".$ty_10_foot_size."',30_10_sleeve_cost = '".$ty_10_sleeve_cost."',30_10_factor='".$ty_10_factor."', 30_10_sleeve_total_cost = '".$ty_10_sleeve_total_cost."',30_10_bbr_weight = '".$ty_10_bbr_weight."',
  40_5_foot_size = '".$f_5_foot_size."',40_5_sleeve_cost = '".$f_5_sleeve_cost."',40_5_factor='".$f_5_factor."', 40_5_sleeve_total_cost = '".$f_5_sleeve_total_cost."',40_5_bbr_weight = '".$f_5_bbr_weight."',
  40_10_foot_size = '".$f_10_foot_size."',40_10_sleeve_cost = '".$f_10_sleeve_cost."',40_10_factor='".$f_10_factor."', 40_10_sleeve_total_cost = '".$f_10_sleeve_total_cost."',40_10_bbr_weight = '".$f_10_bbr_weight."',
  50_5_foot_size = '".$fy_5_foot_size."',50_5_sleeve_cost = '".$fy_5_sleeve_cost."',50_5_factor='".$fy_5_factor."', 50_5_sleeve_total_cost = '".$fy_5_sleeve_total_cost."',50_5_bbr_weight = '".$fy_5_bbr_weight."',
  50_10_foot_size = '".$fy_10_foot_size."',50_10_sleeve_cost = '".$fy_10_sleeve_cost."',50_10_factor='".$fy_10_factor."', 50_10_sleeve_total_cost = '".$fy_10_sleeve_total_cost."',50_10_bbr_weight = '".$fy_10_bbr_weight."',
  60_5_foot_size = '".$s_5_foot_size."',60_5_sleeve_cost = '".$s_5_sleeve_cost."',60_5_factor='".$s_5_factor."', 60_5_sleeve_total_cost = '".$s_5_sleeve_total_cost."',60_5_bbr_weight = '".$s_5_bbr_weight."',
  60_10_foot_size = '".$s_10_foot_size."',60_10_sleeve_cost = '".$s_10_sleeve_cost."',60_10_factor='".$s_10_factor."', 60_10_sleeve_total_cost = '".$s_10_sleeve_total_cost."',60_10_bbr_weight = '".$s_10_bbr_weight."',
  80_5_foot_size = '".$e_5_foot_size."',80_5_sleeve_cost = '".$e_5_sleeve_cost."',80_5_factor='".$e_5_factor."', 80_5_sleeve_total_cost = '".$e_5_sleeve_total_cost."',80_5_bbr_weight = '".$e_5_bbr_weight."',
  80_10_foot_size = '".$e_10_foot_size."',80_10_sleeve_cost = '".$e_10_sleeve_cost."',80_10_factor='".$e_10_factor."', 80_10_sleeve_total_cost = '".$e_10_sleeve_total_cost."',80_10_bbr_weight = '".$e_10_bbr_weight."',
  100_5_foot_size = '".$h_5_foot_size."',100_5_sleeve_cost = '".$h_5_sleeve_cost."',100_5_factor='".$h_5_factor."', 100_5_sleeve_total_cost = '".$h_5_sleeve_total_cost."',100_5_bbr_weight = '".$h_5_bbr_weight."',
  100_10_foot_size = '".$h_10_foot_size."',100_10_sleeve_cost = '".$h_10_sleeve_cost."',100_10_factor='".$h_10_factor."', 100_10_sleeve_total_cost = '".$h_10_sleeve_total_cost."',100_10_bbr_weight = '".$h_10_bbr_weight."',
  120_5_foot_size = '".$ot_5_foot_size."',120_5_sleeve_cost = '".$ot_5_sleeve_cost."',120_5_factor='".$ot_5_factor."', 120_5_sleeve_total_cost = '".$ot_5_sleeve_total_cost."',120_5_bbr_weight = '".$ot_5_bbr_weight."',
  120_10_foot_size = '".$ot_10_foot_size."',120_10_sleeve_cost = '".$ot_10_sleeve_cost."',120_10_factor='".$ot_10_factor."', 120_10_sleeve_total_cost = '".$ot_10_sleeve_total_cost."',120_10_bbr_weight = '".$ot_10_bbr_weight."',
  150_10_foot_size = '".$of_10_foot_size."',150_10_sleeve_cost = '".$of_10_sleeve_cost."',150_10_factor='".$of_10_factor."', 150_10_sleeve_total_cost = '".$of_10_sleeve_total_cost."',150_10_bbr_weight = '".$of_10_bbr_weight."',
  bus_bar_cost = '".$bbr_total_cost."',
  tin_cost = '".$tin_cost."',
  bus_bar_weight = '".$bbr_total_weight."',
  sleeve_cost = '".$total_sleeve_cost."'
  WHERE pannel_id = '".$pannel_costing_id."'";
 
 if (mysqli_query($conn, $sql)) {
    echo json_encode(array("gauges_total_weight"=>$gauges_total_weight,"total_square_feet"=>$total_square_feet,
"s_by_sf"=>$s_by_sf, "sheet_consume"=>$sheet_consume, "mlt_gauge_pr"=>$mlt_gauge_pr, "twelve_SWG"=>$twelve_SWG,
"f_SWG"=>$f_SWG, "sixtn_SWG"=>$sixtn_SWG, "atn_SMG"=>$atn_SMG, "twenty_SMG"=>$twenty_SMG, "bbr_total_weight"=>$bbr_total_weight,
"bbr_total_cost"=>$bbr_total_cost,"tin_cost"=>$tin_cost,"total_sleeve_cost"=>$total_sleeve_cost,));die;
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }

}

