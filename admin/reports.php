
<?php 
	$general_sales_overview_start_date='';
	$general_sales_overview_end_date='';
	$general_sales_overview_total_products =0;
	$general_sales_overview_total_coupons = 0;
	$general_sales_overview_coupon_amount = 0;
	$general_sales_overview_number_of_item_sold = 0;
	$general_sales_overview_total_sales_amount 	= 0;
	$general_sales_overview_refund_issued = 0;
	$general_sales_overview_total_refund_amount = 0;
	$general_sales_overview_net_sales_amount = 0; 

    $individual_selected_month = 1;
	$individual_selected_year = 2011;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') 
	{
	    if(isset($_POST['simpleecommcart-task']) && $_POST['simpleecommcart-task'] == 'general sales') {
			 $data=$_POST['general'];
			 $general_sales_overview_start_date=$data["start_date"];
			 $general_sales_overview_end_date=$data["end_date"]; 
			  
			 $order = new SimpleEcommCartOrder();
			 $dtStart=new DateTime($general_sales_overview_start_date);
			 $dtEnd=new DateTime($general_sales_overview_end_date);
			 $orders = $order->getOrdersByDateRange($dtStart->format('Y-m-d'),$dtEnd->format('Y-m-d'));
			 
			 $general_sales_overview_total_products=0;
			 $general_sales_overview_number_of_item_sold=0;
			 $general_sales_overview_total_sales_amount=0;
			 
			 foreach($orders as $o)
			 { 
				$currentOrder=new SimpleEcommCartOrder($o->id);
				
				if($currentOrder->payment_status != 'Pending')
				{
					$order_items = $currentOrder->getItems(); 
					foreach($order_items as $item)
					{
						$general_sales_overview_total_products += $item->quantity;
						$general_sales_overview_number_of_item_sold+=$item->quantity;
					}
					
					$general_sales_overview_total_sales_amount += $o->total;
				} 
				if($currentOrder->payment_status == 'Refund')
				{
					$general_sales_overview_refund_issued++;
					$general_sales_overview_total_refund_amount += $o->total;
				}
				if($currentOrder->coupon != 'none')
				{
					$general_sales_overview_total_coupons++;
					$general_sales_overview_coupon_amount+=$currentOrder->discount_amount;
				}
			 }
			 
			 $general_sales_overview_net_sales_amount=$general_sales_overview_total_sales_amount
			 		- $general_sales_overview_total_refund_amount;
					
			 //TO DO COUPON and REFUND
			 if($_POST['export'])
			 {
			 	//export 
			 }
			 else
			 {
			 	
				
			 }
		}
		else if(isset($_POST['simpleecommcart-task']) && $_POST['simpleecommcart-task'] == 'individual product sales') { 
		 	$data=$_POST['individual'];
			$individual_selected_month=$data["month"];
			$individual_selected_year=$data["year"];
		}
	}
?>
<h2>Reports</h2>
<div class='wrap' style="width:80%;max-width:80%;float:left;"> 
<div id="widgets-left" style="margin-right: 5px;">
    <div id="available-widgets">
			
		<div class="widgets-holder-wrap">
			<div class="sidebar-name"> 
         		<h3><?php _e( 'General Overview' , 'simpleecommcart' ); ?></h3>
        	</div>
       		<div class="widget-holder"> 
			<form class='phorm' action="" method="post">
    			 	<input type='hidden' name='simpleecommcart-task' value='general sales'/> 
				 	<table  style="width:100%;">
					 	<tr>
							<th>
								<span>Start Date</span>
							</th>
							<td>
								<input  type="text" name="general[start_date]" id="general_start_date" value="<?php echo $general_sales_overview_start_date ?>"/>
								 (YYYY-mm-dd)
								
							</td>
							<th>
								<span>End Date</span>
							</th>
							<td>
								<input  type="text" name="general[end_date]" id="general_end_date" value="<?php echo $general_sales_overview_end_date ?>"/>
								 (YYYY-mm-dd)
							</td>
							<td>
								<input name="submit" type="submit" value="Submit"/>
							</td>
							<td>
							<!--	<input name="export"  type="submit" value="Export full report"/>-->
							</td>
						</tr>
						 
				 	</table>
				 </form>
				 <table  class="widefat" style="width:250px;" >
				 	 
					<tr>
						<th>Number of items sold </th>
						<td><?php echo $general_sales_overview_number_of_item_sold ?></td>
					</tr>
					<tr>
						<th>Total sales amount</th>
						<td><?php echo  SIMPLEECOMMCART_CURRENCY_SYMBOL .$general_sales_overview_total_sales_amount  ?></td>
					</tr> 
					<tr>
						<th>Coupon Used</th>
							<td><?php echo $general_sales_overview_total_coupons ?></td>
					</tr>
					<tr>
						<th>Coupon Amount</th>
							<td><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL .$general_sales_overview_coupon_amount ?></td>
					</tr>
				 </table>
				 <br/>
			</div>
		</div>
		<div class="widgets-holder-wrap">
			<div class="sidebar-name"> 
         		<h3><?php _e( 'Monthly Sales Report' , 'simpleecommcart' ); ?></h3>
        	</div>
       		<div class="widget-holder">
				<form class='phorm' action="" method="post">
    			<input type='hidden' name='simpleecommcart-task' value='monthly sales'/> 
				<?php
					$current_year =  strftime('%Y');
					if (isset($_POST['monthly_sales_current_year']) )  {
    					$current_year = $_POST['monthly_sales_current_year'];
	 				}
					
					$order = new SimpleEcommCartOrder();
					
					
					$chart = new LineChart(500, 250);
					$dataSet = new XYDataSet();
					
					$tmp=0;
					foreach($order->getCompletedOrdersByMonth(1,$current_year) as $o) $tmp+=$o->total; 
					$dataSet->addPoint(new Point("Jan", $tmp));
					
					$tmp=0;
					foreach($order->getCompletedOrdersByMonth(2,$current_year) as $o) $tmp+=$o->total; 
					$dataSet->addPoint(new Point("Feb", $tmp));
					
					$tmp=0;
					foreach($order->getCompletedOrdersByMonth(3,$current_year) as $o) $tmp+=$o->total; 
					$dataSet->addPoint(new Point("Mar", $tmp));
					
					$tmp=0;
					foreach($order->getCompletedOrdersByMonth(4,$current_year) as $o) $tmp+=$o->total; 
					$dataSet->addPoint(new Point("Apr", $tmp));
					
					$tmp=0;
					foreach($order->getCompletedOrdersByMonth(5,$current_year) as $o) $tmp+=$o->total; 
					$dataSet->addPoint(new Point("May", $tmp));
					
					$tmp=0;
					foreach($order->getCompletedOrdersByMonth(6,$current_year) as $o) $tmp+=$o->total; 
					$dataSet->addPoint(new Point("Jun", $tmp));
					
					$tmp=0;
					foreach($order->getCompletedOrdersByMonth(7,$current_year) as $o) $tmp+=$o->total; 
					$dataSet->addPoint(new Point("July", $tmp));
					
					$tmp=0;
					foreach($order->getCompletedOrdersByMonth(8,$current_year) as $o) $tmp+=$o->total; 
					$dataSet->addPoint(new Point("Aug", $tmp));
					
					$tmp=0;
					foreach($order->getCompletedOrdersByMonth(9,$current_year) as $o) $tmp+=$o->total; 
					$dataSet->addPoint(new Point("Sep", $tmp));
					
					$tmp=0;
					foreach($order->getCompletedOrdersByMonth(10,$current_year) as $o) $tmp+=$o->total; 
					$dataSet->addPoint(new Point("Oct", $tmp));
					
					$tmp=0;
					foreach($order->getCompletedOrdersByMonth(11,$current_year) as $o) $tmp+=$o->total; 
					$dataSet->addPoint(new Point("Nov", $tmp));
					
					$tmp=0;
					foreach($order->getCompletedOrdersByMonth(12,$current_year) as $o) $tmp+=$o->total; 
					$dataSet->addPoint(new Point("Dec", $tmp)); 
					 
					$chart->setDataSet($dataSet);
					$chart->setTitle("Total sales amount for ".$current_year);
					$chart->render(SIMPLEECOMMCART_PATH.'ChartFolder' . DIRECTORY_SEPARATOR ."monthly".$current_year.".png");
				?> 
				<table>
					<tr>
						<td>
							<select name="monthly_sales_current_year" id="monthly_sales_current_year">
								<?php
									for($i= 2011; $i<=2025; $i++)
									{
										$selected='';
										if($i == $current_year)$selected='selected="selected"';
										?>
										<option value="<?php echo $i ?>" <?php echo $selected?>><?php echo $i ?></option>
										<?php
									}
								?>
							</select>
						</td>
						<td>
							<input name="showreport"  type="submit" value="Show Report"/>
						</td>
					</tr>
				</table>
  				<br>
				<img alt="Sales" src="<?php echo SIMPLEECOMMCART_URL.'/ChartFolder/monthly'.$current_year.'.png' ?>" style="border: 1px solid gray;"/>
				</form>
				<br/>
			</div>
		</div>
		<div class="widgets-holder-wrap">
			<div class="sidebar-name"> 
         		<h3><?php _e( 'Daily Sales Report' , 'simpleecommcart' ); ?></h3>
        	</div>
       		<div class="widget-holder">
				 <?php if(SIMPLEECOMMCART_PRO): ?> 
				<form class='phorm' action="" method="POST">
					<input type='hidden' name='simpleecommcart-task' value='daily sales'/> 
				
				<?php
				  global $wpdb;
				  $data = array();
				    
				  $current_year_daily_sales=strftime('%Y'); 
				  $current_month_daily_sales = strftime('%m') +0;
				  
				  if (isset($_POST['daily_sales_current_year']) )  {
    					$current_year_daily_sales = $_POST['daily_sales_current_year'];
	 			  }
				  if (isset($_POST['daily_sales_current_month']) )  {
    					$current_month_daily_sales = $_POST['daily_sales_current_month'];
	 			  }
				  for($i=0; $i<32; $i++) {
				    //$dayStart = date('Y-m-d 00:00:00', strtotime('today -' . $i . ' days', SimpleEcommCartCommon::localTs()));
				    $dayStart = date(''.$current_year_daily_sales.'/'.$current_month_daily_sales.'/'.$i);
				    //$dayEnd   = date('Y-m-d 00:00:00', strtotime("$dayStart +1 day",SimpleEcommCartCommon::localTs()));
				    $dayEnd   = date(''.$current_year_daily_sales.'/'.$current_month_daily_sales.'/'.($i+1));
					//echo '$dayEnd: '.$dayEnd.'<br>';
				    $orders = SimpleEcommCartCommon::getTableName('orders');
				    $sql = "SELECT sum(`total`) from $orders where  payment_status='Complete' and  ordered_on > '$dayStart' AND ordered_on < '$dayEnd'";
					//echo $sql.'<br>';
				    $dailyTotal = $wpdb->get_var($sql);
				    $data['days'][$i] = date('m/d/Y', strtotime($dayStart, SimpleEcommCartCommon::localTs()));
				    $data['totals'][$i] = $dailyTotal;
				  }
				?>
				
				<table style="width:100px;">
					 	<tr>
							<td>
								<select name="daily_sales_current_month">
									<option value="1" <?php echo  $current_month_daily_sales==1?'selected':''?>>January</option>
									<option value="2" <?php echo  $current_month_daily_sales==2?'selected':''?>>February</option>
									<option value="3" <?php echo  $current_month_daily_sales==3?'selected':''?>>March</option>
									<option value="4" <?php echo  $current_month_daily_sales==4?'selected':''?>>April</option>
									<option value="5" <?php echo  $current_month_daily_sales==5?'selected':''?>>May</option>
									<option value="6" <?php echo  $current_month_daily_sales==6?'selected':''?>>June</option>
									<option value="7" <?php echo  $current_month_daily_sales==7?'selected':''?>>July</option>
									<option value="8" <?php echo  $current_month_daily_sales==8?'selected':''?>>August</option>
									<option value="9" <?php echo  $current_month_daily_sales==9?'selected':''?>>September</option>
									<option value="10" <?php echo  $current_month_daily_sales==10?'selected':''?>>October</option>
									<option value="11" <?php echo  $current_month_daily_sales==11?'selected':''?>>November</option>
									<option value="12" <?php echo  $current_month_daily_sales==12?'selected':''?>>December</option>
								</select>
							</td>
							<td>
								 <select name="daily_sales_current_year"> 
									<option value="2011" <?php echo  $current_year_daily_sales==2011?'selected':''?>>2011</option>
									<option value="2012" <?php echo  $current_year_daily_sales==2012?'selected':''?>>2012</option>
									<option value="2013" <?php echo  $current_year_daily_sales==2013?'selected':''?>>2013</option>
									<option value="2014" <?php echo  $current_year_daily_sales==2014?'selected':''?>>2014</option>
									<option value="2015" <?php echo  $current_year_daily_sales==2015?'selected':''?>>2015</option>
									<option value="2016" <?php echo  $current_year_daily_sales==2016?'selected':''?>>2016</option>
									<option value="2017" <?php echo  $current_year_daily_sales==2017?'selected':''?>>2017</option>
									<option value="2018" <?php echo  $current_year_daily_sales==2018?'selected':''?>>2018</option>
									<option value="2019" <?php echo  $current_year_daily_sales==2019?'selected':''?>>2019</option>
									<option value="2020" <?php echo  $current_year_daily_sales==2020?'selected':''?>>2020</option>
								 </select>
							</td>
							<td>
								<input name="showReportDailySales" type="submit" value="Show Report"/>
							</td>
						</tr>
				</table>
<br/>
				<table class="SimpleEcommCartTableMed"> 
				  <?php
				  	$back_colors = array(0 => "#ffc90e", 1 => "#ff7f27", 2=>"#22b14c", 3=> "#b97a57", 4=>"#7f7f7f",5=>"#7092be",6=>"#00a2e8"); 
				  ?> 
				  <?php for($i=0; $i<count($data['days']); $i++): ?>
				    <?php if($i % 7 == 0) { echo '<tr>'; } ?>
				    <td style="background-color:<?php  echo $back_colors[$i % 7] ?>;">
				      <span style="color: #000; font-size: 11px;">
				      <?php echo date('m/d/Y D', strtotime($data['days'][$i], SimpleEcommCartCommon::localTs())); ?></span><br/>
				      <span style="font-weight:bold;"> <?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL . number_format($data['totals'][$i], 2); ?></span>
				
				    </td>
				    <?php if($i % 7 == 6) { echo '</tr>'; } ?>
				  <?php endfor; ?>
				</table>
				<?php endif; ?>
</form><br/>
			</div>
		</div>
		<div class="widgets-holder-wrap">
			<div class="sidebar-name"> 
         		<h3><?php _e( 'Individual Product Sales Report' , 'simpleecommcart' ); ?></h3>
        	</div>
       		<div class="widget-holder">
			<form class='phorm' action="" method="POST">
				<input type='hidden' name='simpleecommcart-task' value='individual product sales'/> 
				<?php
				 if($_SERVER['REQUEST_METHOD'] != 'POST') 
				 {
					$individual_selected_month = strftime('%m') +0;;
					$individual_selected_year = strftime('%Y');
				 }
				?>
				<table style="width:100px;">
					 	<tr>
							<td>
								<select name="individual[month]">
									<option value="1" <?php echo  $individual_selected_month==1?'selected':''?>>January</option>
									<option value="2" <?php echo  $individual_selected_month==2?'selected':''?>>February</option>
									<option value="3" <?php echo  $individual_selected_month==3?'selected':''?>>March</option>
									<option value="4" <?php echo  $individual_selected_month==4?'selected':''?>>April</option>
									<option value="5" <?php echo  $individual_selected_month==5?'selected':''?>>May</option>
									<option value="6" <?php echo  $individual_selected_month==6?'selected':''?>>June</option>
									<option value="7" <?php echo  $individual_selected_month==7?'selected':''?>>July</option>
									<option value="8" <?php echo  $individual_selected_month==8?'selected':''?>>August</option>
									<option value="9" <?php echo  $individual_selected_month==9?'selected':''?>>September</option>
									<option value="10" <?php echo  $individual_selected_month==10?'selected':''?>>October</option>
									<option value="11" <?php echo  $individual_selected_month==11?'selected':''?>>November</option>
									<option value="12" <?php echo  $individual_selected_month==12?'selected':''?>>December</option>
								</select>
							</td>
							<td>
								 <select name="individual[year]">
									<option value="2010" <?php echo  $individual_selected_year==2010?'selected':''?>>2010</option>
									<option value="2011" <?php echo  $individual_selected_year==2011?'selected':''?>>2011</option>
									<option value="2012" <?php echo  $individual_selected_year==2012?'selected':''?>>2012</option>
									<option value="2013" <?php echo  $individual_selected_year==2013?'selected':''?>>2013</option>
									<option value="2014" <?php echo  $individual_selected_year==2014?'selected':''?>>2014</option>
									<option value="2015" <?php echo  $individual_selected_year==2015?'selected':''?>>2015</option>
									<option value="2016" <?php echo  $individual_selected_year==2016?'selected':''?>>2016</option>
									<option value="2017" <?php echo  $individual_selected_year==2017?'selected':''?>>2017</option>
									<option value="2018" <?php echo  $individual_selected_year==2018?'selected':''?>>2018</option>
									<option value="2019" <?php echo  $individual_selected_year==2019?'selected':''?>>2019</option>
									<option value="2020" <?php echo  $individual_selected_year==2020?'selected':''?>>2020</option>
								 </select>
							</td>
							<td>
								<input name="showReport" type="submit" value="Show Report"/>
							</td>
						</tr>
				</table>
			</form>
			<br/>
			<?php 
			    
				$products=array();
				
				/*$product=new SimpleEcommCartProduct();
				$allProducts=$product->getModels();
				foreach($allProducts as $p)
				{
				
					$products[$p->id]= array('name'=>$p->name,
											 'price'=>$p->price,
											 'quantity'=>0);
				} */
				
				$order = new SimpleEcommCartOrder();
				$orders = $order->getOrdersByMonth($individual_selected_month,$individual_selected_year);
				foreach($orders as $o)
				{
					$currentOrder=new SimpleEcommCartOrder($o->id);
			    	$order_items = $currentOrder->getItems(); 
					foreach($order_items as $item)
					{
					   if($products[$item->description]==NULL)
					   {
					   		$product_name = $item->description; 
					   		$products[$item->description]= array('name'=>$product_name,
																'price'=>$item->product_price,
																'quantity'=>0);
					   } 
					   $products[$item->description]['quantity'] += $item->quantity; 
					}
				} 
			?>
			<?php
			$chart = new PieChart(500, 250);
	        	$dataSet = new XYDataSet();
				foreach($products as $p)
				{
					if($p['quantity']>0)
					{
						$dataSet->addPoint(new Point($p['name'].'('.$p['quantity'].')',$p['quantity']));
					}
				} 
				$chart->setDataSet($dataSet);
	 			$chart->setTitle("Product Sales Quantity Stat");
				$chart->render(SIMPLEECOMMCART_PATH.'ChartFolder' . DIRECTORY_SEPARATOR ."individual".$individual_selected_month.".png");
			?>
			<img alt="Sales" src="<?php echo SIMPLEECOMMCART_URL.'/ChartFolder/individual'.$individual_selected_month.'.png' ?>" style="border: 1px solid gray;"/>
			<br/>
			<!--<table class="widefat" style="width:500px;">
				<thead>
					<th>Product Name</th>
					<th>Sales Price</th>
					<th>Quantity Sold</th>
					<th>Refund</th>
					<th>Sales Total</th>
				</thead>
				<tbody>
					<?php
						foreach($products as $p)
						{
							if($p['quantity']>0)
							{
								echo '<tr>';
								echo '<td>'.$p['name'].'</td>';
								echo '<td>'.SIMPLEECOMMCART_CURRENCY_SYMBOL.$p['price'].'</td>';
								echo '<td>'.$p['quantity'].'</td>';
								echo '<td>0</td>';
								echo '<td>'.SIMPLEECOMMCART_CURRENCY_SYMBOL.number_format($p['price'] * $p['quantity'], 2, '.', '').'</td>';
								echo '</tr>';
							} 
						}
					?>
				</tbody>
			</table>-->
			<br/>
			</div>
		</div>
	</div>
</div>	
</div>
<div style="float:right;width:18%;max-width:18%">
	<?php
	 	echo SimpleEcommCartCommon::getView('admin/more.php',NULL);
	?>
</div>
<div style="clear:both;"/>
 