<?php
	function queryString($str,$val)
	{
		$queryString = array();
		$queryString = $_GET;
		$queryString[$str] = $val;
		$queryString = "?".htmlspecialchars(http_build_query($queryString),ENT_QUOTES);
 		return $queryString;
}

?>

<style type="text/css">
	.gallery-list
	{
		 
	}
	.gallery-list .container
	{ 
		clear:both;
		width:100%;
		padding-top:10px; 
		padding-bottom:10px; 
	} 
	.gallery-list .container .imgContainer
	{
		width:25%;
		float:left;
		padding-top:5px;
	}
	.gallery-list .container  .imgContainer img
	{
		width:98%; 
	}
	.gallery-list .container .descContainer
	{
		width:74%;
		float:left;
		padding-left:5px;
	}
	
	.gallery-grid
	{
		 
	}
	.gallery-grid .container
	{  
		width:33%;
		padding-top:10px; 
		padding-bottom:10px; 
		float:left;
	} 
	.gallery-grid .container .imgContainer
	{
		 
	}
	.gallery-grid .container .imgContainer img
	{
		width:98%; 
	}
	.gallery-grid .container .descContainer
	{
		width:74%;
		float:left;
		padding-left:5px;
	}
	
	ul.pager
	{
		list-style-type: none;
		margin:0px;
	padding:0px;
	}
	.pager li {display: inline; }
	.pager li a {padding:5px; border:solid 1px #ccc;margin:2px;}
	.current
	{
		color:black;
		
	}
	.current:hover
	{
		text-decoration:none;
	}
	#pager
	{
		clear:both;
	}
</style>
<script type="text/javascript">
	function goToCatPage()
	{
		var categoryId = jQuery('#category').val();
		if(categoryId== 0 )
		{
			var pageUrl= "<?php 
							$storePage = get_page_by_path('store'); 
							$url_main = get_permalink($storePage->ID);
							echo $url_main;
						  ?>";
			window.location = pageUrl;
		}
		else{
			var pageUrl= "<?php	
							$storePage = get_page_by_path('store'); 
							$url = get_permalink($storePage->ID);
							if(strpos($url, '?')) 
							{
								$url.='&catid="+categoryId+"';
							}
							else
							{ 
								$url.='?catid="+categoryId+"';
							} 
							echo $url;
						 ?>"; 
			window.location = pageUrl;
		}
	}
</script>
Category:
<select id="category" name="category" onchange="goToCatPage();">
	 
	<option value="0" >All</option>
	<?php 
		$cat = new SimpleEcommCartProductCategory();
		$cats = $cat->getModels(); 
	?>
	<?php foreach($cats as $c): ?> 
		<?php 
			$selected='';
			if(isset( $_GET['catid']))
			{
				if( $_GET['catid'] == $c->id)
					$selected =  'selected="selected"';
			}
		?>
		<option value="<?php echo $c->id ?>"  <?php echo $selected ?> ><?php echo $c->name.'('.$c->getProductCount($c->id).')' ?></option>
	<?php endforeach; ?> 
</select>

<?php 
	if(SimpleEcommCartSetting::getValue("display_products")=="list")
	{
		?>
			<div class="gallery-list"> 
	<?php
		$product= new SimpleEcommCartProduct(); 
		$products = $product->getModels();
		
		$item_count = 0;
		$page_size = 12;
		$page_index = 0;
		
		if(isset($_GET['index']))
		{
			$page_index = $_GET['index'] + 0;
		}
		
		$item_index=0;
	?>
	<?php foreach($products as $p): ?> 
		<?php
			if(isset( $_GET['catid']))
			{
				if($p->category == $_GET['catid'])
				{
					//do nothing 
				}
				else
				{
					continue;
				}
			}
			
			$item_count++;
		?>
		
		<?php
			//skip other pages
			$start= ($page_index * $page_size)+0;
			$end = $start+$page_size - 1;
			
			if($item_index>=$start  && $item_index<=$end )
			{
				$item_index++;
			}
			else	
			{
				$item_index++;
				continue;
			}
			
		?>  
		<div class="container">
			<div class="imgContainer">
				<?php
				  
					$path='';
					if(!empty($p->product_image_path)) 
					{ 
						$upload_dir = wp_upload_dir(); 
						$path = $upload_dir['baseurl'].'/simpleecommcart/digitalproduct/'.$p->product_image_path;
					} 
				?> 
				<img  src="<?php echo $path ?>"/>
			</div>
			<div class="descContainer">
				<div class="name">
					<h2 style="color:#000;text-transform:uppercase;margin:0;"><?php echo $p->name; ?></h2>
				</div>
				<div class="desc">
					<?php echo $p->description; ?>
				</div>
				<div class="shortcode">
					<?php
						$attrs= array('id' => $p->id);
						$content='';
						echo SimpleEcommCartShortcodeManager::showCartButton($attrs,$content);	 
					?>
				</div>
			</div>
		</div>  
				
		
	<?php endforeach; ?> 
</div>
<div id="pager">
	<?php 
		if($item_count>0 )
		{
			$page_count = intval(ceil($item_count/$page_size));
			if($page_count>1)
			{
				echo '<ul class="pager">';
				for($i=0;$i<$page_count;$i++)
				{
					echo '<li>';
					if($i==$page_index)
					{
						echo '<a class="current">'.($i+1).'</a>';
					}
					else
					{ 
						$storePage = get_page_by_path('store'); 
						$url = get_permalink($storePage->ID);
						if(strpos($url, '?')) 
						{
							$substring_index =  strpos($url, '?') -1;
							$url=substr($url,0,$substring_index+1);
						}
					 
						$url = $url. queryString('index',$i);
				 
						echo '<a href="'.$url.'" class="link">'.($i+1).'</a>';
					}
					echo '</li>';
				}
				echo '</ul>';
			}
		}
		
	
	?>
</div>
		<?php
	}
	else
	{
		?>
<div class="gallery-grid"> 
	<?php
		$product= new SimpleEcommCartProduct(); 
		$products = $product->getModels();
		$col_count=0;
		
		$item_count = 0;
		$page_size = 18;
		$page_index = 0;
		
		if(isset($_GET['index']))
		{
			$page_index = $_GET['index'] + 0;
		}
		
		$item_index=0;
	?>
	<?php foreach($products as $p): ?> 
		<?php
			if(isset( $_GET['catid']))
			{
				if($p->category == $_GET['catid'])
				{
					//do nothing
				}
				else
				{
					continue;
				}
				
			}
			
			$item_count++;
		?> 
		<?php
			//skip other pages
			$start= ($page_index * $page_size)+0;
			$end = $start+$page_size - 1;
			
			if($item_index>=$start  && $item_index<=$end )
			{
				$item_index++;
			}
			else	
			{
				$item_index++;
				continue;
			}
			
		?>   
		<div class="container">
			<div class="imgContainer">
				<?php
				  
					$path='';
					if(!empty($p->product_image_path)) 
					{
						$upload_dir = wp_upload_dir(); 
						$path = $upload_dir['baseurl'].'/simpleecommcart/digitalproduct/'.$p->product_image_path;
					} 
				?> 
				<img  src="<?php echo $path ?>"/>
			</div>
			<div class="descContainer">
				<div class="name">
					<h2 style="color:#000;text-transform:uppercase;margin:0;"><?php echo $p->name; ?></h2>
				</div>
				<div class="desc">
					<?php echo $p->description; ?>
				</div>
				<div class="shortcode">
					<?php
						$attrs= array('id' => $p->id);
						$content='';
						echo SimpleEcommCartShortcodeManager::showCartButton($attrs,$content);	 
					?>
				</div>
			</div>
		</div>  
		<?php
			$col_count++;
			if($col_count >= 3)
			{
				$col_count = 0;
				?>
				<div style="clear:both;"></div>
				<?php
			}
		?>
	<?php endforeach; ?> 
</div>
<div id="pager" style="clear:both;">
	<?php 
		if($item_count>0 )
		{
			$page_count = intval(ceil($item_count/$page_size));
			if($page_count>1)
			{
				echo '<ul class="pager">';
				for($i=0;$i<$page_count;$i++)
				{
					echo '<li>';
					if($i==$page_index)
					{
						echo '<a class="current">'.($i+1).'</a>';
					}
					else
					{ 
						$storePage = get_page_by_path('store'); 
						$url = get_permalink($storePage->ID);
						if(strpos($url, '?')) 
						{
							$substring_index =  strpos($url, '?') -1;
							$url=substr($url,0,$substring_index+1);
						}
					 
						$url = $url. queryString('index',$i);
				 
						echo '<a href="'.$url.'" class="link">'.($i+1).'</a>';
					}
					echo '</li>';
				}
				echo '</ul>';
			}
		}
	
	?>
</div>
		<?php
	}
?>
