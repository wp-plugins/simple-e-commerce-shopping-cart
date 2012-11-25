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
		padding-top:10px;
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
</style>
 
 

<?php 
    $cat_id=$data['id'];
	 
	
	if(SimpleEcommCartSetting::getValue("display_products")=="list")
	{
		?>
			<div class="gallery-list"> 
	<?php
		$product= new SimpleEcommCartProduct(); 
		$products = $product->getModels();
	?>
	<?php foreach($products as $p): ?> 
		<?php
			 
				if($p->category == $cat_id)
				{
					//do nothing
				}
				else
				{
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
	?>
	<?php foreach($products as $p): ?> 
		<?php
			 
				if($p->category == $cat_id)
				{
					//do nothing
				}
				else
				{
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
		<?php
	}
?>
