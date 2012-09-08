<?php
$product = new SimpleEcommCartProduct();
$product_category = new SimpleEcommCartProductCategory();
$adminUrl = get_bloginfo('wpurl') . '/wp-admin/admin.php';
$errorMessage = false;


if($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['simpleecommcart-action'] == 'save product') {
  try {
    $product->handleFileUpload();
    $product->setData($_POST['product']);
 
	if(empty($product->item_number)) 
	{ 
		$product->itemNumber = $product->getMaximumProductId()+1;
	}
    $product->save();
    $product->clear();
  }
  catch(SimpleEcommCartException $e) {
    $errorCode = $e->getCode();
    if($errorCode == 66102) {
      // Product save failed
      $errors = $product->getErrors();
      $errorMessage = SimpleEcommCartCommon::showErrors($errors, "<p><b>" . __("The product could not be saved for the following reasons","simpleecommcart") . ":</b></p>");
    }
    elseif($errorCode == 66101) {
      // File upload failed
      $errors = $product->getErrors();
      $errorMessage = SimpleEcommCartCommon::showErrors($errors, "<p><b>" . __("The file upload failed","simpleecommcart") . ":</b></p>");
    }
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product save failed ($errorCode): " . strip_tags($errorMessage));
  }
  
}
elseif(isset($_GET['task']) && $_GET['task'] == 'edit' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = SimpleEcommCartCommon::getVal('id');
  $product->load($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = SimpleEcommCartCommon::getVal('id');
  $product->load($id);
  $product->deleteMe();
  $product->clear();
  
  $product->deleteAllInventoryData($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'xdownload' && isset($_GET['id']) && $_GET['id'] > 0) {
  // Load the product
  $id = SimpleEcommCartCommon::getVal('id');
  $product->load($id);
  
  // Delete the download file
  $setting = new SimpleEcommCartSetting();
  $dir = SimpleEcommCartSetting::getValue('product_folder');
  $path = $dir . DIRECTORY_SEPARATOR . $product->download_path;
  unlink($path);
  
  // Clear the name of the download file from the object and database
  $product->download_path = '';
  $product->save();
}
elseif(isset($_GET['task']) && $_GET['task'] == 'deleteProductImage' && isset($_GET['id']) && $_GET['id'] > 0) {
  // Load the product
  $id = SimpleEcommCartCommon::getVal('id');
  $product->load($id);
  
  // Delete the download file
  $setting = new SimpleEcommCartSetting();
  $dir = SimpleEcommCartSetting::getValue('product_folder');
  $path = $dir . DIRECTORY_SEPARATOR . $product->product_image_path;
  unlink($path);
  
  // Clear the name of the download file from the object and database
  $product->product_image_path = '';
  $product->save();
} 
elseif(isset($_GET['task']) && $_GET['task'] == 'deleteButtonImage' && isset($_GET['id']) && $_GET['id'] > 0) {
  // Load the product
  $id = SimpleEcommCartCommon::getVal('id');
  $product->load($id);
  
  // Delete the download file
  $setting = new SimpleEcommCartSetting();
  $dir = SimpleEcommCartSetting::getValue('product_folder');
  $path = $dir . DIRECTORY_SEPARATOR . $product->button_image_path;
  unlink($path);
  
  // Clear the name of the download file from the object and database
  $product->button_image_path = '';
  $product->save();
}
elseif(isset($_GET['task']) && $_GET['task'] == 'deleteVariation' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = SimpleEcommCartCommon::getVal('id');
  $variationNo = SimpleEcommCartCommon::getVal('variationNo');
  $variation = SimpleEcommCartCommon::getVal('variation');
  $ikey =  SimpleEcommCartCommon::getVal('ikey');
  
  $product->load($id);
  
  if($variationNo == '1')
  { 
	  	$variation1s=array_filter(explode("|",$product->variation1_variations));
		$prices1s=array_filter(explode("|",$product->variation1_prices));
		$sign1s=array_filter(explode("|",$product->variation1_signs));
		
		 
		$variation1sString='';
		$prices1sString='';
		$sign1sString='';
	 
	    $variation_count = count($variation1s);
		for($i=0;$i< $variation_count ;$i++)
		{
		 	if($variation1s[$i]===$variation)
			{
				 //do nothing
			}
			else
			{
				$variation1sString.=$variation1s[$i];
				$prices1sString.=$prices1s[$i];
				$sign1sString.=$sign1s[$i];
				 
				$variation1sString.='|';
				$prices1sString.='|';
				$sign1sString.='|'; 
			}
		} 
		$variation1sString = substr($variation1sString,0,-1);
		$prices1sString = substr($prices1sString,0,-1);
		$sign1sString = substr($sign1sString,0,-1);
		
		$product->variation1_variations = $variation1sString;
		$product->variation1_prices = $prices1sString;
		$product->variation1_signs = $sign1sString; 
  }
  else if($variationNo == '2')
  {
  		$variation2s=array_filter(explode("|",$product->variation2_variations));
		$prices2s=array_filter(explode("|",$product->variation2_prices));
		$sign2s=array_filter(explode("|",$product->variation2_signs));
		
		 
		$variation2sString='';
		$prices2sString='';
		$sign2sString='';
	 
	    $variation_count = count($variation2s);
		for($i=0;$i< $variation_count ;$i++)
		{
		 	if($variation2s[$i]===$variation)
			{
				 //do nothing
			}
			else
			{
				$variation2sString.=$variation2s[$i];
				$prices2sString.=$prices2s[$i];
				$sign2sString.=$sign2s[$i];
				 
				$variation2sString.='|';
				$prices2sString.='|';
				$sign2sString.='|'; 
			}
		} 
		$variation2sString = substr($variation2sString,0,-1);
		$prices2sString = substr($prices2sString,0,-1);
		$sign2sString = substr($sign2sString,0,-1);
		
		$product->variation2_variations = $variation2sString;
		$product->variation2_prices = $prices2sString;
		$product->variation2_signs = $sign2sString; 
  } 
  
  $product->save();
  $product->deleteInventoryByiKey($ikey);
}
?>

<?php if($errorMessage): ?>
<div style="margin: 30px 50px 10px 5px;"><?php echo $errorMessage ?></div>
<?php endif; ?>


<h2>Add/Edit Products</h2>
<div class='wrap' style="width:80%;max-width:80%;float:left;"> 
<form action="" method="post" enctype="multipart/form-data">
  <input type="hidden" name="simpleecommcart-action" value="save product" />
  <input type="hidden" name="product[id]" value="<?php echo $product->id ?>" />
  <div id="widgets-left" style="margin-right: 5px;">
    <div id="available-widgets">

	 <div class="widgets-holder-wrap">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e( 'Product Details' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
            <ul>
              <li>
                <label class="long" for="product-name"><?php _e( 'Product Name' , 'simpleecommcart' ); ?>:</label>
                <input class="long" type="text" name='product[name]' id='product-name' value='<?php echo $product->name ?>' />
				<img title="Name of the product" src=" <?php echo INFO_ICON ?>"/>
				 <input type="hidden" name='product[item_number]' id='product-item_number' value='<?php echo $product->itemNumber ?>' />
              </li>
            

                
              <li>
                <label class="long" for="product-price" id="price_label"><?php _e( 'Product Price' , 'simpleecommcart' ); ?>:</label>
                <input type='text' style="width: 75px;" id="product-price" name='product[price]' value='<?php echo $product->price ?>'>
                <img title="Enter Product price to two decimal places. Examples: 12.00 or 5.95 or 2012.50 etc. Do not put currency symbol in the price." src=" <?php echo INFO_ICON ?>"/>

                <span class="label_desc" id="price-description"></span>
              </li>
			    <li>
                <label class="long" for="product-price_description" id="price_description_label"><?php _e( 'Sales Pitch' , 'simpleecommcart' ); ?>:</label>
                <input type='text' style="width: 275px;" id="product-price_description" name='product[price_description]' value='<?php echo $product->priceDescription ?>'>
                <img title="Add a sales pitch that will appear before the product price. Example: “Great Offer 50% off regular price”" src=" <?php echo INFO_ICON ?>"/>
                <span class="label_desc" id="price_description"><?php _e( 'If you would like to customize the display of the price' , 'simpleecommcart' ); ?></span>
              </li>
            </ul>
          </div>
        </div>
      </div>
	 <div class="widgets-holder-wrap closed">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e( 'Digital Product Details' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
              <ul>
			   <li>
                <label class="long" for='product-upload'><?php _e( 'Upload Downloadable File' , 'simpleecommcart' ); ?>:</label>
                <input class="long" type='file' name='product[upload]' id='product-upload' value='' />
				<input id="btnClearProductUpload" type="button" value="Clear"/>
	                        <img title="Select the downloadable file that you are selling. All the uploaded files are stored at wordpress\wp-content\plugins\simpleecommcart\digitalproducts" src=" <?php echo INFO_ICON ?>"/>

				<input type="hidden" value='<?php echo $product->download_path?>' id="product-download_path" name="product[download_path]" class="long">
              </li>
			  <?php
			  	if(!empty($product->download_path))
				{
				?>
			  <li>
			  <div style="color:green;">
			   <span>Uploaded File Name:</span><em><?php echo $product->download_path?></em>
			   <a href='?page=simpleecommcart-products&task=xdownload&id=<?php echo $product->id ?>'>Delete this file from the server</a>
			  </div>
			 
			  </li>
			  <?php
			  	
				}
			  ?>
			   <li>
                <label class="long" for='product-digital_prdoduct_url'><em><?php _e( 'or' , 'simpleecommcart' ); ?></em> <?php _e( 'File URL' , 'simpleecommcart' ); ?>:</label>
                <input class="long" type='text' name='product[digital_prdoduct_url]' id='product-digital_prdoduct_url' value='<?php echo $product->digital_prdoduct_url ?>' /> 
                <img title="If the downloadable file is stored elsewhere (eg Amazon S3), put the URL of the downloadable file here." src=" <?php echo INFO_ICON ?>"/>

              </li>
              <li>
                <label class="long" for='product-download_limit'><?php _e( 'Download limit' , 'simpleecommcart' ); ?>:</label>
                <input style="width: 35px;" type='text' name='product[download_limit]' id='product-download_limit' value='<?php echo ($product->download_limit == NULL || $product->download_limit=='0' )? '': $product->download_limit ?>' />
                <img title="Number of times customers may download the files. Leave the box empty for unlimited download." src=" <?php echo INFO_ICON ?>"/>



              </li>
            </ul>
          </div>
        </div>
      </div>
 	 <div class="widgets-holder-wrap closed">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e( 'Additional Product Details' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
          	<ul>
			   <li>
			   <div style="padding-left:85px;">
			   	<table>
					<tr>
						<td align="left" valign="top">
						  <label  for='product-description'><?php _e( 'Product Description' , 'simpleecommcart' ); ?>:</label>
						</td>
						<td  align="left" valign="top">
						<textarea class="long" rows="3" cols="40" type="text" name='product[description]' id='product-description'><?php echo $product->description ?></textarea> 
				 <img title="Product description will be displayed on Store Page. By default plugin creates a Store page. Set up a store page from Settings-> Store and Page Settings." src=" <?php echo INFO_ICON ?>"/>
						</td>
					</tr>
				</table>
			   </div>
              
				
              </li>
			   <li>
                <label class="long" for='product_image_upload'><?php _e( 'Product Image' , 'simpleecommcart' ); ?>:</label>
                <input class="long" type='file' name='product[image_upload]' id='product_image_upload' value='' /><input id="btnClearProductImageUpload" type="button" value="Clear"/>
				<img title="Select an image for the product." src=" <?php echo INFO_ICON ?>"/>
             <input type="hidden" value='<?php echo $product->product_image_path?>' id="product-product_image_path" name="product[product_image_path]" class="long">
              </li>
			  <?php
			  	if(!empty($product->product_image_path))
				{
				?>
			  <li>
			  <div style="color:green;" id="dvProductImageServerFile">
			   <span>Uploaded Product Image File Name:</span><em><?php echo $product->product_image_path?></em>
			   <a href='?page=simpleecommcart-products&task=deleteProductImage&id=<?php echo $product->id ?>'>Delete this file from the server</a>
			  </div>
			 
			  </li>
			  <?php
			  	
				}
			  ?>
			   <li>
                <label class="long" for='product-category'><?php _e( 'Product Category' , 'simpleecommcart' ); ?>:</label>
                <select name="product[category]" id="product-category">
				<option  value="0" <?php echo ($product->category == '0')? 'selected="selected"' : '' ?>>Select</option>
				<?php
					$product_categories = $product_category->getModels();
					foreach($product_categories as $category)
					{
						if($product->category==$category->id)
						{
							echo '<option value="'.$category->id.'" selected="selected">'.$category->name.'</option>';
						}
						else
						{
							echo '<option value="'.$category->id.'">'.$category->name.'</option>';
						}
						
					}
				?>		 
				</select>
				or  <a href='?page=simpleecommcart-settings'>Create new Category</a>
				<img title="Categories allow you to group all your products so it is easy to find them.Create a category from Settings-> Product Categories" src=" <?php echo INFO_ICON ?>"/>
              </li>
			   <li>
                <label class="long" for='product-button_image_upload'><?php _e( 'Custom Button Image' , 'simpleecommcart' ); ?>:</label>
                <input class="long" type='file' name='product[button_image_upload]' id='product-button_image_upload' value='' /><input id="btnClearButtonImageUpload" type="button" value="Clear"/><img title="Select a custom button for the product or leave it blank and SimpleEcommCart’s default “add to cart” button will be applied." src=" <?php echo INFO_ICON ?>"/>
              <input type="hidden" value='<?php echo $product->button_image_path?>' id="product-button_image_path" name="product[button_image_path]" class="long">
              </li>
			  <?php
			  	if(!empty($product->button_image_path))
				{
				?>
			  <li>
			  <div style="color:green;" id="dvButtonImageServerFile">
			   <span>Uploaded Button Image File Name:</span><em><?php echo $product->button_image_path?></em>
			   <a href='?page=simpleecommcart-products&task=deleteButtonImage&id=<?php echo $product->id ?>'>Delete this file from the server</a>
			  </div>
			 
			  </li>
			  <?php
			  	
				}
			  ?>
 <li>
                <label class="long" for='product-collect_customer_input'><?php _e( 'Collect Customer’s Instruction?' , 'simpleecommcart' ); ?>:</label>
               	<input type="radio" value="single" name="product[custom]" <?php echo ($product->custom=='single')?'checked="checked"':'' ?> />Yes
				<input type="radio" value="none" name="product[custom]" <?php echo ($product->custom==NULL || $product->custom=='none')?'checked="checked"':'' ?>/>No
				 <img title="If you require to collect information from your customer regarding the product then select yes." src=" <?php echo INFO_ICON ?>"/>
              </li>
			   <li>
                <label class="long" for='product-custom_desc'><?php _e( 'Instructions for customers' , 'simpleecommcart' ); ?>:</label>
                <input class="long" type="text" name='product[custom_desc]' id='product-custom_desc' value="<?php echo $product->custom_desc ?>" />
				<img title="Tell you customer what information to enter in the text box. Example: “Please enter the name you want to print on the coffee mug”" src=" <?php echo INFO_ICON ?>"/>
              </li>
			  </ul>
          </div>
        </div>
     </div>
 <div class="widgets-holder-wrap closed">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e('Product Variations' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div> 
		   	<div>
		   	<h4>Product Variation 1 &nbsp;&nbsp;<img title="Create a variation for the product. Example: Color, Size, Feature, Versions etc." src=" <?php echo INFO_ICON ?>"/></h4>
			 <ul> 
			 	<li>
					 <label class="long" for='product-variation1_name'><?php _e( 'Variation Name' , 'simpleecommcart' ); ?>:</label>
	                 <input  type="text" name="product[variation1_name]"  id='product-variation1_name' value='<?php echo $product->variation1_name ?>'/> <img title="Name of the Variation. Example: Color, Size etc" src=" <?php echo INFO_ICON ?>"/>
				</li>
				 
			 	<li>
					 <label class="long" for='variation1'><?php _e( 'Value' , 'simpleecommcart' ); ?>:</label>
	                 <input  type="text"  id='variation1'/> 
					 <img title="Value for the variation. Example: If your variation name is Color then put a color such as Blue in the value." src=" <?php echo INFO_ICON ?>"/>
				</li>
				<li>
					 <label class="long" for='additionalprice1'><?php _e( 'Price' , 'simpleecommcart' ); ?>( <?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?>):</label>
	                <input  type="text" id='additionalprice1'/>  
					 <select name="sign1" id="sign1">
					 	<option value="+" >+</option>
						<option value="-" >-</option>
					 </select>
					  <a id="addvariation1button" href='#'> Add  Variation</a>
					  <img title="For each individual variation value you can add an additional price or enter “0”. Example: If you r are selling books you can charge +$5 extra for new editions or charge -$5 less for old editions." src=" <?php echo INFO_ICON ?>"/>
				</li>
				 
			 </ul>
			 <h4 id="variation1_name_label">&nbsp;</h4>
			 <table id="variation1Table" class="widefat" style="width:40%;">
			 	<tbody>
				</tbody>
			 </table>
			<input class="long" type="hidden" name='product[variation1_variations]' id='product-variation1_variations' value='<?php echo $product->variation1_variations ?>'  />
			<input class="long" type="hidden" name='product[variation1_prices]' id='product-variation1_prices' value='<?php echo $product->variation1_prices ?>'	/>	
			<input class="long" type="hidden" name='product[variation1_signs]' id='product-variation1_signs' value='<?php echo $product->variation1_signs ?>'	/>	
		   </div>
		    <div>
		   	<h4>Product Variation 2 &nbsp;&nbsp; </h4>
			 <ul> 
			 	<li>
					 <label class="long" for='product-variation2_name'><?php _e( 'Variation Name' , 'simpleecommcart' ); ?>:</label>
	                 <input  type="text" name="product[variation2_name]"  id='product-variation2_name' value='<?php echo $product->variation2_name ?>'/>
				</li>
				 
			 	<li>
					 <label class="long" for='variation2'><?php _e( 'Value' , 'simpleecommcart' ); ?>:</label>
	                 <input  type="text"  id='variation2'/> 
					 
				</li>
				<li>
					 <label class="long" for='additionalprice2'><?php _e( 'Price' , 'simpleecommcart' ); ?>( <?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?>):</label>
	                <input  type="text" id='additionalprice2'/>  
					 <select name="sign2" id="sign2">
					 	<option value="+" >+</option>
						<option value="-" >-</option>
					 </select>
					  <a id="addvariation2button" href='#'> Add  Variation</a>
					   
				</li>
				 
			 </ul>
			 <h4 id="variation2_name_label"></h4>
			 <table id="variation2Table" class="widefat" style="width:40%;">
			 	<tbody>
				</tbody>
			 </table>
			<input class="long" type="hidden" name='product[variation2_variations]' id='product-variation2_variations' value='<?php echo $product->variation2_variations ?>'  />
			<input class="long" type="hidden" name='product[variation2_prices]' id='product-variation2_prices' value='<?php echo $product->variation2_prices ?>'	/>	
			<input class="long" type="hidden" name='product[variation2_signs]' id='product-variation2_signs' value='<?php echo $product->variation2_signs ?>'	/>	
		   </div>
          </div>
        </div>
      </div> 
	 
 	 
	 <div class="widgets-holder-wrap closed">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e('Tax' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
           <ul>
		   	<li>
			 <label class="long" for="product-taxable"><?php _e( 'Apply Tax' , 'simpleecommcart' ); ?>:</label>
                 <input type="radio" value="1"  id="product_taxable_yes"  name="product[taxable]"  <?php echo ($product->taxable == '1')? 'checked="true"' : '' ?>>Yes</input>
			     <input type="radio" value="0"  id="product_taxable_no" name="product[taxable]" <?php echo (($product->id==0)||($product->taxable == '0'))? 'checked="true"' : '' ?> >No</input>&nbsp;&nbsp;
				 <img title="Do you want to collect sales tax for this product? If yes then configure tax settings from Tax Menu." src=" <?php echo INFO_ICON ?>"/>
			</li>
			 
		   </ul>
          </div>
        </div>
      </div> 
	 <div class="widgets-holder-wrap closed">
        <div class="sidebar-name">
          <div class="sidebar-name-arrow"><br/></div>
          <h3><?php _e('Shipping' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
        </div>
        <div class="widget-holder">
          <div>
             <ul>
		   	<li>
			 <label class="long" for="product-shipped"><?php _e( 'Shipping Required' , 'simpleecommcart' ); ?>:</label>
               <input type="radio" value="1" id="product-shipped_yes"   name="product[shipped]"  <?php echo ($product->shipped == '1')? 'checked="true"' : '' ?>>Yes</input>
			     <input type="radio" value="0" id="product-shipped_no" name="product[shipped]"   <?php echo (($product->shipped == '0')||($product->shipped == NULL))? 'checked="true"' : '' ?>>No</input>
				 &nbsp;<img title="Does this product require shipping? If yes then configure Shipping conditions from Shipping Menu." src=" <?php echo INFO_ICON ?>"/>


			</li>
			</ul>
			<div id="product-shipped_yes_div">
				<div>
				<h4 style="padding-left: 10px;"><?php _e( 'Flat Shipping Rate Local' , 'simpleecommcart' ); ?>&nbsp;&nbsp;<img title="Local flat shipping rate for individual product (Shipping within the country based on your base location)." src=" <?php echo INFO_ICON ?>"/></h4>
				<ul>
					<li>
					 <label class="long" for='product-single_sihipping_cost'><?php _e( 'Single Rate' , 'simpleecommcart' ); ?>:</label>
	                <?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><input  type="text" name='product[single_sihipping_cost]' style="width: 50px;" id='product-single_sihipping_cost' value='<?php echo $product->single_sihipping_cost ?>'/> 
					<img title="When there is one product in the Cart single rate is applied. Only enter value no $ sign." src=" <?php echo INFO_ICON ?>"/> 
					</li>
						<li>
					 <label class="long" for='product-multiple_sihipping_cost'><?php _e( 'Bundle Rate' , 'simpleecommcart' ); ?>:</label>
	                 <?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><input  type="text" name='product[multiple_sihipping_cost]' style="width: 50px;" id='product-multiple_sihipping_cost' value='<?php echo $product->multiple_sihipping_cost ?>'/> 
					<img title="For more than one product in the Cart bundle rate is applied. Only enter value no $ sign." src=" <?php echo INFO_ICON ?>"/> 
					</li>
				</ul>
			</div>												
				<div>
				<h4 style="padding-left: 10px;"><?php _e( 'Flat Shipping Rate International' , 'simpleecommcart' ); ?>&nbsp;&nbsp;<img title="International flat shipping rate for individual product (Shipping outside the country based on your base location)." src=" <?php echo INFO_ICON ?>"/></h4>
				<ul>
					<li>
					 <label class="long" for='product-single_sihipping_cost_international'><?php _e( 'Single Rate' , 'simpleecommcart' ); ?>:</label>
	                 <?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><input  type="text" name='product[single_sihipping_cost_international]' style="width: 50px;" id='product-single_sihipping_cost_international' value='<?php echo $product->single_sihipping_cost_international ?>'/> 
					</li>
						<li>
					 <label class="long" for='product-multiple_sihipping_cost_international'><?php _e( 'Bundle Rate' , 'simpleecommcart' ); ?>:</label>
	                 <?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><input  type="text" name='product[multiple_sihipping_cost_international]' style="width: 50px;" id='product-multiple_sihipping_cost_international' value='<?php echo $product->multiple_sihipping_cost_international ?>'/>  
					</li>
				</ul>
				<p>
					<span style="font-size:10pt;font-weight:bold;">Product Specific Shipping (Logic):</span>
					<table class="widefat" style="width:600px;">
						<thead>
							<tr>
								<th>Product</th>
								<th>Single Shipping Rate</th>
								<th>Bundle Shipping Rate</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>T-shirt</td>
								<td>$10</td>
								<td>$5</td>
							</tr>
							<tr>
								<td>Pants</td>
								<td>$15</td>
								<td>$7</td>
							</tr>
							<tr>
								<td>Laptop</td>
								<td>$35</td>
								<td>$20</td>
							</tr>
						</tbody>
					</table>
				</p>
<p>
					<span><strong>Scenario1</strong>: 3 T-shirts in the shopping cart:</span>
					<table class="widefat" style="width:600px;">
						<thead>
							<tr>
								<th>Product</th>
								<th>Shipping</th>
								<th>Logic</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>T-shirt</td>
								<td>$10</td>
								<td>the most expensive single shipping rate of the products in the cart</td>
							</tr>
							<tr>
								<td>T-shirt</td>
								<td>$5</td>
								<td>Bundle rate</td>
							</tr>
							<tr>
								<td>T-shirt</td>
								<td>$5</td>
								<td>Bundle rate</td>
							</tr>
							<tr>
								<td>Shipping Cost</td>
								<td>$20</td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</p>
				<p>
					<span><strong>Scenario2</strong>: 1 T-shirt, 1 Pants and 1 Laptop in the shopping cart:</span>
					<table class="widefat" style="width:600px;">
						<thead>
							<tr>
								<th>Product</th>
								<th>Shipping</th>
								<th>Logic</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Laptop</td>
								<td>$35</td>
								<td>the most expensive single shipping rate of the products in the cart</td>
							</tr>
							<tr>
								<td>Pants</td>
								<td>$7</td>
								<td>Bundle rate</td>
							</tr>
							<tr>
								<td>T-shirt</td>
								<td>$5</td>
								<td>Bundle rate</td>
							</tr>
							<tr>
								<td>Shipping Cost</td>
								<td>$47</td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</p>
				 
				<p>
					*Local Rates apply when you are shipping within the country
					<br>
*International Rates apply when you are shipping outside the country

				</p>
			</div>												
			</div>
          </div>
        </div>
      </div>  
      
     <div style="padding: 0px;">
        <?php if($product->id > 0): ?>
        <a href='?page=simpleecommcart-products' class='button-secondary linkButton' style=""><?php _e( 'Cancel' , 'simpleecommcart' ); ?></a>
        <?php endif; ?>
        <input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' />
      </div>
  
    </div>
  </div>

</form>


<?php
  $product = new SimpleEcommCartProduct();
  $products = $product->getNonSubscriptionProducts();
  if(count($products)):
?>
  <h2 style="margin-top: 20px;"><?php _e( 'Product List' , 'simpleecommcart' ); ?></h2>
  <table class="widefat" style="width: 95%">
  <thead>
    <tr>
      <th colspan="8">Search: <input type="text" name="SimpleEcommCartAccountSearchField" value="" id="SimpleEcommCartAccountSearchField" /></th>
    </tr>
  	<tr>
  		<th>ID</th> 
		<th>Image</th> 
  		<th>Product Name</th>
		<th>Category</th>
  		<th>Price</th>
  		<th>Tax</th>
  		<th>Shipping</th>
  		<th>Actions</th>
  	</tr>
  </thead>
  <tbody>
    <?php foreach($products as $p): ?>
     <tr>
       <td><?php echo $p->id ?></td> 
	   <td>
	   	<?php
					$path='';
					if(!empty($p->product_image_path)) 
					{ 
						$upload_dir = wp_upload_dir(); 
						$path = $upload_dir['baseurl'].'/simpleecommcart/digitalproduct/'.$p->product_image_path;
					} 
				?> 
				<img style="width:60px;"  src="<?php echo $path ?>"/>
	   </td>
       <td><?php echo $p->name ?>
         <?php
           if($p->gravityFormId > 0 && isset($gfTitles) && isset($gfTitles[$p->gravityFormId])) {
             echo '<br/><em>Linked To Gravity Form: ' . $gfTitles[$p->gravityFormId] . '</em>';
           }
          ?>
       </td>
	   <td> 
	   	<?php
	   		if($p->category == '0')
			{
				echo '-';
			}
			else
			{
				$cat=new SimpleEcommCartProductCategory($p->category);
				echo $cat->name;
			}
		?>
	   </td>
       <td><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo $p->price ?></td>
       <td><?php echo $p->taxable? ' Yes' : 'No'; ?></td>
       <td><?php echo $p->shipped? ' Yes' : 'No'; ?></td>
       <td>
         <a href='?page=simpleecommcart-products&task=edit&id=<?php echo $p->id ?>'>Edit</a> | 
         <a class='delete' href='?page=simpleecommcart-products&task=delete&id=<?php echo $p->id ?>'>Delete</a>
       </td>
     </tr>
    <?php endforeach; ?>
  </tbody>
  </table>
<?php endif; ?>

<?php
  $products = $product->getSpreedlyProducts();
  if(count($products)):
?>
<h2 style="margin-top: 20px;"><?php _e( 'Your Spreedly Subscription Products' , 'simpleecommcart' ); ?></h2>
<table class="widefat" style="width: 95%">
<thead>
  <tr>
    <th colspan="8">Search: <input type="text" name="SimpleEcommCartAccountSearchField" value="" id="SimpleEcommCartAccountSearchField" /></th>
  </tr>
	<tr>
		<th>ID</th>
		<th>Image</th> 
		<th>Item Number</th>
		<th>Product Name</th>
		<th>Category</th>
		<th>Price</th>
		<th>Tax</th>
		<th>Shipping</th>
		<th>Actions</th>
	</tr>
</thead>
<tbody>
  <?php foreach($products as $p): ?>
   <tr>
     <td><?php echo $p->id ?></td>
	  <td>
	   	<?php
			$path='';
			if(!empty($p->product_image_path)) 
			{
				$upload_dir = wp_upload_dir(); 
				$path = $upload_dir['baseurl'].'/simpleecommcart/digitalproduct/'.$p->product_image_path;
			} 
		?> 
		<img style="width:60px;"  src="<?php echo $path ?>"/>
	   </td>
     <td><?php echo $p->itemNumber ?></td>
     <td><?php echo $p->name ?>
       <?php
         if($p->gravityFormId > 0 && isset($gfTitles) && isset($gfTitles[$p->gravityFormId])) {
           echo '<br/><em>Linked To Gravity From: ' . $gfTitles[$p->gravityFormId] . '</em>';
         }
        ?>
     </td>
	 <td><?php
	   		if($p->category == '0')
			{
				echo '-';
			}
			else
			{
				$cat=new SimpleEcommCartProductCategory($p->category);
				echo $cat->name;
			}
		?></td>
     <td><?php echo $p->getPriceDescription() ?></td>
     <td><?php echo $p->taxable? ' Yes' : 'No'; ?></td>
     <td><?php echo $p->shipped? ' Yes' : 'No'; ?></td>
     <td>
       <a href='?page=simpleecommcart-products&task=edit&id=<?php echo $p->id ?>'>Edit</a> | 
       <a class='delete' href='?page=simpleecommcart-products&task=delete&id=<?php echo $p->id ?>'>Delete</a>
     </td>
   </tr>
  <?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

</div>
<div style="float:right;width:18%;max-width:18%">
	<?php
	 	echo SimpleEcommCartCommon::getView('admin/more.php',NULL);
	?>
</div>
<div style="clear:both;"/>
<script type='text/javascript'>
  jQuery.noConflict();
  jQuery(document).ready(function($) {
     
    toggleSubscriptionText();
    toggleMembershipProductAttrs();
    
    $('.sidebar-name').click(function() {
      $(this.parentNode).toggleClass("closed");
    });
    
    $("#product-feature_level").keydown(function(e) {
      if (e.keyCode == 32) {
        $(this).val($(this).val() + ""); // append '-' to input
        return false; // return false to prevent space from being added
      }
    }).change(function(e) {
        $(this).val(function (i, v) { return v.replace(/ /g, ""); }); 
    });
    
    $("#product-spreedly_subscription_id").change(function(){
      if($(this).val() != 0){
        $(".userPriceSettings, .isUserPrice").hide();
        $("#product-is_user_price").val("0");
      }
      else{
        $(".isUserPrice").show();
        if($(".isUserPrice").val() == 1){
          $(".userPriceSettings").show();
        }
      }
    })
    
    $("#product-is_user_price").change(function(){
      if($(this).val() == 1){
        $(".userPriceSettings").show();
      }
      if($(this).val() == 0){
        $(".userPriceSettings").hide();
      }
    })

    $('.delete').click(function() {
      return confirm('Are you sure you want to delete this item?');
    });

    // Ajax to populate gravity_form_qty_id when gravity_form_id changes
    $('#product-gravity_form_id').change(function() {
      var gravityFormId = $('#product-gravity_form_id').val();
      $.get(ajaxurl, { 'action': 'update_gravity_product_quantity_field', 'formId': gravityFormId}, function(myOptions) {
        $('#gravity_form_qty_id >option').remove();
        $('#gravity_form_qty_id').append( new Option('None', 0) );
        $.each(myOptions, function(val, text) {
            $('#gravity_form_qty_id').append( new Option(text,val) );
        });
      });
    });
    
    $('#spreedly_subscription_id').change(function() {
      toggleSubscriptionText();
    });
    
    $('#paypal_subscription_id').change(function() {
      toggleSubscriptionText();
    });
    
    $('#SimpleEcommCartAccountSearchField').quicksearch('table tbody tr');
    
    $('#product-membership_product').change(function() {
      toggleMembershipProductAttrs();
    });
    
    $('#product-lifetime_membership').click(function() {
      toggleLifeTime();
    });
    
    $('#viewLocalDeliverInfo').click(function() {
      $('#localDeliveryInfo').toggle();
      return false;
    });
    
    $('#amazons3ForceDownload').click(function() {
      $('#amazons3ForceDownloadAnswer').toggle();
      return false;
    });
    
    //<?php if(SimpleEcommCartSetting::getValue('amazons3_id')): ?>
    //validateS3BucketName();  
    //<?php endif; ?>
    //$("#product-s3_bucket, #product-s3_file").blur(function(){
    //   validateS3BucketName();        
    //})
   
   
   $('#btnClearProductUpload').click(function(){
   		$('#product-upload').val('');
   });
   $('#btnClearProductImageUpload').click(function(){
   		$('#product_image_upload').val('');
		$('#product-product_image_path').val('');
		$('#dvProductImageServerFile').hide();
   });
   $('#btnClearButtonImageUpload').click(function(){
   		$('#product-button_image_upload').val('');
		$('#product-button_image_path').val('');
		$('#dvButtonImageServerFile').hide();
   });
   // variation
   initProductVariation();
    
   //tax
   initTax();
	
   //shipping
   initShipping();
  });
  
  function toggleLifeTime() {
    if(jQuery('#product-lifetime_membership').attr('checked')) {
      jQuery('#product-billing_interval').val('');
      jQuery('#product-billing_interval').attr('disabled', true);
      jQuery('#product-billing_interval_unit').val('days');
      jQuery('#product-billing_interval_unit').attr('disabled', true);
    }
    else {
      jQuery('#product-billing_interval').attr('disabled', false);
      jQuery('#product-billing_interval_unit').attr('disabled', false);
    }
  }
  
  function toggleMembershipProductAttrs() {
    if(jQuery('#product-membership_product').val() == '1') {
      jQuery('.member_product_attrs').css('display', 'block');
    }
    else {
      jQuery('.member_product_attrs').css('display', 'none');
    }
  }
  
  function toggleSubscriptionText() {
    if(isSubscriptionProduct()) {
      jQuery('#price_label').text('One Time Fee:');
      jQuery('#price_description').text('One time fee charged when subscription is purchased. This could be a setup fee.');
      jQuery('#subscriptionVariationDesc').show();
      jQuery('.nonSubscription').hide();
      jQuery('#membershipProductFields').hide();
      jQuery('#product-membership_product').val(0);
      jQuery('#product-feature_level').val('');
      jQuery('#product-billing_interval').val('');
      jQuery('#product-billing_interval_unit').val('days');
      jQuery('#product-lifetime_membership').removeAttr('checked');
    }
    else {
      jQuery('#price_label').text('Price:');
      jQuery('#price_description').text('');
      jQuery('#subscriptionVariationDesc').hide();
      jQuery('.nonSubscription').show();
      jQuery('#membershipProductFields').show();
    }
  }
  
  function isSubscriptionProduct() {
    var spreedlySubId = jQuery('#spreedly_subscription_id').val();
    var paypalSubId = jQuery('#paypal_subscription_id').val();
    
    if(spreedlySubId > 0 || paypalSubId > 0) {
      return true;
    }
    return false;
  }
  
 /* function bucketError(message){
    jQuery(".bucketNameLabel").css('color','#ff0000');
    // check for existing message
    if(jQuery(".simpleecommcartS3BucketRestrictions").html().indexOf(message) == -1){
      jQuery(".simpleecommcartS3BucketRestrictions").append("<li>" + message + "</li>");
    }
  }*/
  
 /* function validateS3BucketName(){
    var rawBucket = jQuery("#product-s3_bucket").val();
  
    // clear errors
    jQuery(".simpleecommcartS3BucketRestrictions li").remove();
    jQuery(".bucketNameLabel").css('color','#000');
    
    // no underscores
    if(rawBucket.indexOf('_') != -1){
      bucketError("Bucket names should NOT contain underscores (_).");
    }
    
    // not empty if there's a file name
    // proper length
    if(rawBucket == "" && jQuery("#product-s3_file").val() != ""){
      bucketError("If you have a file name, you'll need a bucket.");
    } 
    else if(rawBucket.length > 0 && (rawBucket.length < 3 || rawBucket.length > 63) ){
      bucketError("Bucket names should be between 3 and 63 characters long.")
    }
    
    // dont end with a dash
    if(rawBucket.substring(rawBucket.length-1,rawBucket.length) == "-"){
      bucketError("Bucket names should NOT end with a dash.");
    }
    
    // dont have dashes next to periods
    if(rawBucket.indexOf('.-') != -1 || rawBucket.indexOf('-.') != -1){
      bucketError("Dashes cannot appear next to periods. For example, “my-.bucket.com” and “my.-bucket” are invalid names.");
    }
    
    // no uppercase characters allowed
    // only letters, numbers, periods or dashes
    i=0;
    while(i <= rawBucket.length-1){
      if (rawBucket.charCodeAt(i) > 64 && rawBucket.charCodeAt(i) < 90) {
      	bucketError("Bucket names should NOT contain UPPERCASE letters.");
      }
      if (rawBucket != "" && !rawBucket.charAt(i).match(/[a-z0-9\.\-]/g) ){
        bucketError("Bucket names may only contain lower case letters, numbers, periods or hyphens.");
      }
      i++;
    }
    
    // must start with letter or number
    if(rawBucket != "" && !rawBucket.substring(0,1).match(/[a-z0-9]/g) ){
      bucketError("Bucket names must begin with a number or a lower-case letter.");
    }
    
    // cannot be an ip address
    if(rawBucket != "" && rawBucket.match(/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/g) ){
      bucketError("Bucket names cannot be an IP address");
    }
    
  }*/
  function initProductVariation()
  {
  	jQuery('#variation1Table').hide();
  	jQuery('#variation2Table').hide();
  
    jQuery('#variation1').val('');
	jQuery('#additionalprice1').val('');
  	 jQuery('#addvariation1button').click(function() {
     	addVariation1();
		populateVariation1Table();
		populateVariation1InputFields();
    });
	
	jQuery('#variation2').val('');
	jQuery('#additionalprice2').val('');
  	 jQuery('#addvariation2button').click(function() {
     	addVariation2();
		populateVariation2Table();
		populateVariation2InputFields();
    });
	
	loadExistingvariations();
  }
  var variation1DataModel=[];
  function addVariation1()
  {  
  	if(jQuery('#variation1').val().length<=0 || jQuery('#additionalprice1').val().length<=0){
		alert('Value and/or Price field is empty');
		return;
	}
  	var item={ 
			  "variation": jQuery('#variation1').val(),
			  "price": jQuery('#additionalprice1').val(),
			  "sign": jQuery('#sign1').val(),
		      };
	variation1DataModel.push(item);
	jQuery('#variation1').val('');
	jQuery('#additionalprice1').val('');
	jQuery('#sign1').val('+');
  }
  function populateVariation1Table()
  {   
	  jQuery('#variation1Table tbody').empty();
	  for(var i=0;i<variation1DataModel.length;i++)
	  {
	  	var model=variation1DataModel[i];
		jQuery('#variation1Table tbody').append('<tr><td>'+model.variation+'</td><td>'+model.price+'</td><td>'+model.sign+'</td><td><a href="#" onClick="deleteVariation1Item('+i+')" >Delete</a></td></tr>');
	  }
	  
	   if(variation1DataModel.length>0)
	  	jQuery('#product-variation1_name').attr('readonly','true');
	  else
	  	jQuery('#product-variation1_name').removeAttr('readonly');
		
	  jQuery('#variation1_name_label').text( jQuery('#product-variation1_name').val());
	  
	  if(variation1DataModel.length>0)
	  {
	  	 jQuery('#variation1Table').show(); 
	  }
	 
  }
  
  function populateVariation1InputFields()
  {
 	 var variation1s="";
	 var price1s="";
	 var sign1s="";
  	 for(var i=0;i<variation1DataModel.length;i++)
	 {
	  	var model=variation1DataModel[i];
		variation1s+=model.variation;
		price1s+=model.price;
		sign1s+=model.sign;
		if(i<variation1DataModel.length-1)
		{
			variation1s+="|";
			price1s+="|";
			sign1s+="|";
		}
	 }
	 jQuery("#product-variation1_variations").val(variation1s);
     jQuery("#product-variation1_prices").val(price1s);
	 jQuery("#product-variation1_signs").val(sign1s);
  }
  function deleteVariation1Item(index)
  { 
 	 variation1DataModel.splice(index, 1); 
	 populateVariation1Table();
	 populateVariation1InputFields();
  }
  
  var variation2DataModel=[];
  function addVariation2()
  {  
  	if(jQuery('#variation2').val().length<=0 || jQuery('#additionalprice2').val().length<=0){
		alert('Value and/or Price field is empty');
		return;
	}
  	var item={ 
			  "variation": jQuery('#variation2').val(),
			  "price": jQuery('#additionalprice2').val(),
			  "sign": jQuery('#sign2').val(),
		      };
	variation2DataModel.push(item);
	jQuery('#variation2').val('');
	jQuery('#additionalprice2').val('');
	jQuery('#sign2').val('+');
  }
  function populateVariation2Table()
  { 
      
	  jQuery('#variation2Table tbody').empty();
	  for(var i=0;i<variation2DataModel.length;i++)
	  {
	  	var model=variation2DataModel[i];
		 jQuery('#variation2Table tbody').append('<tr><td>'+model.variation+'</td><td>'+model.price+'</td><td>'+model.sign+'</td><td><a href="#" onClick="deleteVariation2Item('+i+')" >Delete</a></td></tr>');
	  }
	  if(variation2DataModel.length>0)
	  	jQuery('#product-variation2_name').attr('readonly','true');
	  else
	  	jQuery('#product-variation2_name').removeAttr('readonly');
	  jQuery('#variation2_name_label').text( jQuery('#product-variation2_name').val());
	  
	    
	  if(variation2DataModel.length>0)
	  {
	  	 jQuery('#variation2Table').show(); 
	  }
  }
  
  function populateVariation2InputFields()
  {
 	 var variation2s="";
	 var price2s="";
	 var sign2s="";
  	 for(var i=0;i<variation2DataModel.length;i++)
	 {
	  	var model=variation2DataModel[i];
		variation2s+=model.variation;
		price2s+=model.price;
		sign2s+=model.sign;
		if(i<variation2DataModel.length-1)
		{
			variation2s+="|";
			price2s+="|";
			sign2s+="|";
		}
	 }
	 jQuery("#product-variation2_variations").val(variation2s);
     jQuery("#product-variation2_prices").val(price2s);
	 jQuery("#product-variation2_signs").val(sign2s);
  }
  function deleteVariation2Item(index)
  {
 	 variation2DataModel.splice(index, 1); 
	 populateVariation2Table();
	 populateVariation2InputFields();
  }
  
  function loadExistingvariations()
  { 
  	var v1s = jQuery("#product-variation1_variations").val();
    var p1s =  jQuery("#product-variation1_prices").val();
	var s1s =  jQuery("#product-variation1_signs").val();
	
	var v1sSplited=v1s.split("|");
	var p1sSplited=p1s.split("|");
	var s1sSplited=s1s.split("|");
	
	if(v1sSplited.length>0 && p1sSplited.length>0 && v1sSplited.length==p1sSplited.length)
	{
		for(var i=0;i<v1sSplited.length;i++)
		{
			if(jQuery.trim( v1sSplited[i]).length>0)
			{
				var item={ 
			  		"variation": v1sSplited[i],
			  		"price": p1sSplited[i],
					"sign":s1sSplited[i]
		      	};
			variation1DataModel.push(item);
			}
			
		}
		populateVariation1Table();
	} 
	
	var v2s = jQuery("#product-variation2_variations").val();
    var p2s =  jQuery("#product-variation2_prices").val();
	var s2s =  jQuery("#product-variation2_signs").val();
	 
	var v2sSplited=v2s.split("|");
	var p2sSplited=p2s.split("|");
	var s2sSplited=s2s.split("|");
	
	if(v2sSplited.length>0 && p2sSplited.length>0 && v2sSplited.length==p2sSplited.length)
	{
		for(var i=0;i<v2sSplited.length;i++)
		{
			if(jQuery.trim( v2sSplited[i]).length>0)
			{
				var item={ 
			  		"variation": v2sSplited[i],
			  		"price": p2sSplited[i],
					"sign":s2sSplited[i]
		      	};
			variation2DataModel.push(item);
			}
			
		}
		populateVariation2Table();
	} 
  }
  function initTax()
  { 
  	 enableDisableProductSpecificTax();
  
  	 jQuery("#product_taxable_yes").click(function() {
    	 enableDisableProductSpecificTax();
     });
	 
	  jQuery("#product_taxable_no").click(function() {
    	 enableDisableProductSpecificTax();
     });
  }
  function enableDisableProductSpecificTax()
  { 
     if( jQuery("#product_taxable_yes").attr("checked") == 'checked')
	 {
	 	jQuery("#product-specific_tax").removeAttr("disabled"); 
	 }
  	 if( jQuery("#product_taxable_no").attr("checked") == 'checked')
	 { 
	 	jQuery("#product-specific_tax").attr("disabled", "disabled"); 
		jQuery("#product-specific_tax").val('');
	 }
  }
  function initShipping()
  { 
  	 enableDisableShippingDiv();
  
  	 jQuery("#product-shipped_yes").click(function() {
    	 enableDisableShippingDiv();
     });
	 
	  jQuery("#product-shipped_no").click(function() {
    	 enableDisableShippingDiv();
     });
  }
  function enableDisableShippingDiv()
  { 
     if( jQuery("#product-shipped_yes").attr("checked") == 'checked')
	 {
	 	jQuery("#product-shipped_yes_div").show();
	 }
  	 if( jQuery("#product-shipped_no").attr("checked") == 'checked')
	 { 
	 	jQuery("#product-shipped_yes_div").hide(); 
		jQuery("#product-single_sihipping_cost").val('');
		jQuery("#product-multiple_sihipping_cost").val('');
		jQuery("#product-single_sihipping_cost_international").val('');
		jQuery("#product-multiple_sihipping_cost_international").val('');
	 }
  }
  //
</script>