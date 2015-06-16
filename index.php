<?php
	error_reporting(E_ALL);
	$_SERVER['MAGE_IS_DEVELOPER_MODE'] = true;

	require_once '../app/Mage.php';
	Mage::app('default');
	Mage::setIsDeveloperMode(true);
	ini_set('display_errors', 1);
	umask(0);
		
	function getOrderStates() {		
		$list = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
		
		return $list;
	}
	
	
	
	function getCountries() {
		$list = Mage::getResourceModel('directory/country_collection')
        	->loadData()
            ->toOptionArray(false);
        return $list;
    }
    
    function getShippingMethods() {
		$methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
	
	    $options = array();
	
	    foreach($methods as $_ccode => $_carrier)
	    {
	        $_methodOptions = array();
	        if($_methods = $_carrier->getAllowedMethods())
	        {
	            foreach($_methods as $_mcode => $_method)
	            {
	                $_code = $_ccode . '_' . $_mcode;
	                $_methodOptions[] = array('value' => $_code, 'label' => $_method);
	            }
	
	            if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
	                $_title = $_ccode;
	
	            $options[] = array('value' => $_methodOptions, 'label' => $_title);
	        }
	    }
	
	    return $options;
    }
    
	function getActivPaymentMethods() {
		$payments = Mage::getSingleton('payment/config')->getActiveMethods();
	
		$methods = array();
	
		foreach ($payments as $paymentCode=>$paymentModel) {
			$paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
			$methods[$paymentCode] = array(
				'label'   => $paymentTitle,
				'value' => $paymentCode,
			);
		}
	
		return $methods;
	
	}  
	
	
	//print_r(getActivPaymentMethods());
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>POS</title>

        <!-- Bootstrap -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="css/bootstrap-datetimepicker.css" />
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <style type="text/css">
			@media (min-width: 768px) {
				.modal-xl {
					width: 90%;
					max-width:1200px;
				}
			}
			.small {
				float: right;
				max-width: 150px;
			}
			.inputerror {
				background-color: #fcf8e3;
				border-color: #f0ad4e;
			}
			.hide {
				display: none;
			}
			.panel-heading a:after {
				font-family:'Glyphicons Halflings';
				content:"\e114";
				float: right;
				color: grey;
			}
			.panel-heading a.collapsed:after {
				content:"\e080";
			} 
			.searchUser {
				cursor: pointer;	
			} 
		</style>
    </head>
    <body>
        <div class="container-fluid">
	        <h1>Magento POS</h1>
			<div class="alert alert-warning alert-dismissible orderComplete hide" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<strong>Warning!</strong> When setting the order to <strong>complete</strong> the order gets <strong>invoiced</strong> and <strong>shipped</strong> automatically.
			</div>
	        <div class="row">
		        <div class="col-md-6">
			        <div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">Order</h3>
						</div>
						<div class="panel-body">
							<form class="form-horizontal">
								<div class="form-group">
									<label class="col-sm-2 control-label">Order Date</label>
									<div class="col-sm-10">
										<div class='input-group date' id='datetimepicker1'>
											<input type='text' class="form-control" />
											<span class="input-group-addon">
												<span class="glyphicon glyphicon-calendar"></span>
											</span>
										</div>										
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">Order Status</label>
									<div class="col-sm-10">
										<select class="form-control orderStatus" name="orderstatus">
											<option value="-1">Please select...</option>
<?php
	$orderStates = getOrderStates();
	for ($i = 0; $i < count($orderStates); $i++) {
		$status = $orderStates[$i]["status"];
		$label = $orderStates[$i]["label"];
		echo '											<option value="' . $status . '">' . $label . '</option>' . PHP_EOL;		
	}	
?>											
										</select>
									</div>
								</div>								
							</form>
						</div>
					</div>
		        </div>		        
		        <div class="col-md-6">
			        <div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">Account Information<span class="pull-right searchUser" data-toggle="modal" data-target=".users"><i class="fa fa-search"></i></span></h3>
						</div>
						<div class="panel-body">
							<form class="form-horizontal">
								<div class="form-group">
									<label class="col-sm-2 control-label">Name</label>
									<div class="col-sm-10">
										<input type="text" class="form-control" id="name" name="name" placeholder="Name">
									</div>									
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">Email</label>
									<div class="col-sm-10">
										<input type="text" class="form-control" id="email" name="email" placeholder="Email">
									</div>									
								</div>								
							</form>
						</div>
					</div>
		        </div>		        
	        </div>
	        <div class="row">
		        <div class="col-md-6">
			        <div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">
								<a data-toggle="collapse" data-target="#collapseBilling" href="#collapseBilling" class="collapsed">Billing Address</a>
							</h3>
						</div>
						<div id="collapseBilling" class="panel-collapse collapse">
							<div class="panel-body">
								<form class="form-horizontal">
									<div class="form-group">
										<label class="col-sm-2 control-label">Name</label>
										<div class="col-sm-5">
											<input type="text" class="form-control" id="bfirstname" name="bfirstname" placeholder="Fristname">
										</div>
										<div class="col-sm-5">
											<input type="text" class="form-control" id="blastname" name="blastname" placeholder="Lastname">
										</div>
									</div>
									<div class="form-group">
										<label for="street" class="col-sm-2 control-label">Street</label>
										<div class="col-sm-10">
											<input type="text" class="form-control" id="bstreet" name="bstreet" placeholder="Street">
										</div>
									</div>
									<div class="form-group">
										<label for="street" class="col-sm-2 control-label">City</label>
										<div class="col-sm-10">
											<input type="text" class="form-control" id="bcity" name="bcity" placeholder="City">
										</div>
									</div>
									<div class="form-group">
										<label for="postcode" class="col-sm-2 control-label">Postcode</label>
										<div class="col-sm-10">
											<input type="text" class="form-control" id="bpostcode" name="bpostcode" placeholder="Postcode">
										</div>
									</div>
									<div class="form-group">
										<label for="country" class="col-sm-2 control-label">Country</label>
										<div class="col-sm-10">
											<select class="form-control" id="bcountry">
												<option value="-1">Please select...</option>
<?php
	$countryList = getCountries();
	for ($i = 0; $i < count($countryList); $i++) {
		$value = $countryList[$i]["value"];
		$label = $countryList[$i]["label"];
		echo '												<option value="' . $value . '">' . $label . '</option>' . PHP_EOL;		
	}
?>										
											</select>
										</div>
									</div>			
								</form>
							</div>
						</div>
					</div>
		        </div>
		        <div class="col-md-6">
			        <div class="panel panel-default">
						<div class="panel-heading">
							
							<h3 class="panel-title">
								<a data-toggle="collapse" data-target="#collapseShipping" href="#collapseShipping" class="collapsed">Shipping Address</a><span class="pull-right"><label><input type="checkbox" name="billing" id="checkbilling">&nbsp;same as billing</label></span>
							</h3>
						</div>
						<div id="collapseShipping" class="panel-collapse collapse">
							<div class="panel-body">
								<form class="form-horizontal">
									<div class="form-group">
										<label class="col-sm-2 control-label">Name</label>
										<div class="col-sm-5">
											<input type="text" class="form-control" id="sfirstname" name="sfirstname" placeholder="Fristname">
										</div>
										<div class="col-sm-5">
											<input type="text" class="form-control" id="slastname" name="slastname" placeholder="Lastname">
										</div>
									</div>
									<div class="form-group">
										<label for="street" class="col-sm-2 control-label">Street</label>
										<div class="col-sm-10">
											<input type="text" class="form-control" id="sstreet" name="sstreet" placeholder="Street">
										</div>
									</div>
									<div class="form-group">
										<label for="street" class="col-sm-2 control-label">City</label>
										<div class="col-sm-10">
											<input type="text" class="form-control" id="scity" name="scity" placeholder="City">
										</div>
									</div>
									<div class="form-group">
										<label for="postcode" class="col-sm-2 control-label">Postcode</label>
										<div class="col-sm-10">
											<input type="text" class="form-control" id="spostcode" name="spostcode" placeholder="Postcode">
										</div>
									</div>
									
									<div class="form-group">
										<label for="country" class="col-sm-2 control-label">Country</label>
										<div class="col-sm-10">
											<select class="form-control" id="scountry">
												<option value="-1">Please select...</option>
<?php
	for ($i = 0; $i < count($countryList); $i++) {
		$value = $countryList[$i]["value"];
		$label = $countryList[$i]["label"];
		echo '												<option value="' . $value . '">' . $label . '</option>' . PHP_EOL;		
	}
?>										
											</select>
										</div>
									</div>						
								</form>
							</div>
						</div>
					</div>
		        </div>		        
	        </div>
	        <div class="row">
		        <div class="col-md-6">
			        <div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">Payment Information</h3>
						</div>
						<div class="panel-body">
							<form class="form-horizontal">
								<div class="form-group">
									<label class="col-sm-2 control-label">Payment</label>
									<div class="col-sm-10">
										<select class="form-control orderStatus" name="orderstatus">
											<option value="-1">Please select...</option>
<?php
	$paymentMethods = getActivPaymentMethods();
	foreach ($paymentMethods as $paymentMethod) {
		$value = $paymentMethod["value"];
		$label = $paymentMethod["label"];
		echo '											<option value="' . $value . '">' . $label . '</option>' . PHP_EOL;		
	}	
?>											
										</select>
									</div>
								</div>								
							</form>
						</div>
					</div>
		        </div>		        
		        <div class="col-md-6">
			        <div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">Shipping &amp; Handeling Information</h3>
						</div>
						<div class="panel-body">
							<form class="form-horizontal">
								<div class="form-group">
									<label for="shipping" class="col-sm-2 control-label">Method</label>
									<div class="col-sm-10">
										<select class="form-control" id="shipping">
											<option value="-1">Please select...</option>
<?php
	$shippingMethods = getShippingMethods();
	for ($i = 0; $i < count($shippingMethods); $i++) {
		$label = $shippingMethods[$i]["label"];
		$value = $shippingMethods[$i]["value"][0]["value"];
		$label2 = $shippingMethods[$i]["value"][0]["label"];
		echo '												<option value="' . $value . '">' . $label . " - " . $label2 . '</option>' . PHP_EOL;		
	}
?>										
										</select>
									</div>
								</div>
							</form>
						</div>
					</div>
		        </div>		        
	        </div>
	        <div class="row">
		        <div class="col-md-12">
			        <div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">Items Ordered<span class="pull-right"><button type="button" class="btn btn-primary btn-xs" href="" data-target=".products" data-toggle="modal">Add Item</button></span></h3>
						</div>
						<table class="table" id="productlist">
							<thead>
								<tr>
									<th>#</th>
									<th>Name</th>
									<th>SKU</th>
									<th>Config</th>
									<th>Qty</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
		        </div>		        		        
	        </div>

            <div class="row">
		        <div class="col-md-12">
			        <div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">Order</h3>
						</div>
						<div class="panel-body">
							<span class="pull-right"><button type="button" class="btn btn-primary btn-lg">Place Order</button></span>
							Options<br />
							Maintain stocks (each product will be increased in stock with the ordered amount).
							
						</div>
			        </div>
		        </div>
	        </div>

        </div>
        
        <!-- Modal: Producs -->
<?php
	$model = Mage::getModel('catalog/product'); //getting product model
	$collection = $model->getCollection(); //products collection
?>		
		<div class="modal fade products" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" style="margin-left: 10px;" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Products <span class="pull-right"><input data-search="product" class="form-control searchInput" type="text" placeholder="Filter"></span></h4>
					</div>						
					<table class="table">
						<thead>
							<tr>
								<th>#</th>
								<th>Name</th>
								<th>SKU</th>
								<th>Config</th>
								<th>Qty</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody id="sproduct">
<?php
	foreach ($collection as $product) {                  
		$model->load($product->getId());
		$pname = $model->getName();
		$sku = $model->getSku();
		$visibility = $model->getVisibility();
		$id = $product->getId();
		$select = '<span id="label' . $id . '">-</span>';
		
		if ($visibility <> 1) {

			$cProduct = Mage::getModel('catalog/product')->load($product->getId());
	        //check if product is a configurable type or not
	        if ($cProduct->getData('type_id') == "configurable") {
	            //get the configurable data from the product
	            $config = $cProduct->getTypeInstance(true);
	            //loop through the attributes
	            foreach($config->getConfigurableAttributesAsArray($cProduct) as $attributes) {
		            $label = $attributes["label"];
		            
					$select = PHP_EOL . '									<span id="label' . $id . '">' . $label . '</span>'. PHP_EOL . "									" . '<select id="select' . $id . '" class="form-control input-sm small" name="super_attribute[' .  $attributes['attribute_id'] . ']" id="attribute' . $attributes['attribute_id'] . '">' . PHP_EOL;
					foreach($attributes["values"] as $values) {
						$select .= "										<option>" . $values["label"] . "</option>" . PHP_EOL;
					}
					$select .= '									</select>' . PHP_EOL . "								";
				}
			}
?>
							<tr>
								<td class="col-md-1 pid"><?php echo $id; ?></td>
								<td class="pname" id="name<?php echo $id; ?>"><?php echo $pname; ?></td>
								<td class="psku" id="sku<?php echo $id; ?>"><?php echo $sku; ?></td>
								<td><?php echo $select; ?></td>
								<td class="col-md-1"><input type="text" class="form-control input-sm pqty" name="qty" id="qty<?php echo $id; ?>"></td>
								<td class="col-md-1"><i class="fa fa-eraser fa-2x"></i>&nbsp;<a href="#" class="productAdd" id="p<?php echo $id; ?>"><i class="fa fa-chevron-right fa-2x"></i></a></td>
							</tr>
<?php			
		}	
	}            
?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- /products -->
		
		<!-- Modal: Users -->
		<div class="modal fade users" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-xl">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" style="margin-left: 10px;" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Customers <span class="pull-right"><input data-search="customer" class="form-control searchInput" type="text" placeholder="Filter"></span></h4>
					</div>						
					<table class="table">
						<thead>
							<tr>
								<th>#</th>
								<th>Name</th>
								<th>Email</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody id="scustomer">
<?php
	// Get all users from store:
	$collection = Mage::getModel('customer/customer')->getCollection();

	// Customer info:
	$customer = Mage::getModel("customer/customer");
	$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
	
	foreach ($collection as $info) {
		$email = $info->getData('email');
		$customer->loadByEmail($email);
		$id = $customer->getId();

		$address_id = $customer->getDefaultBilling();
		$city = "";
		$street = "";
		$postcode = "";
		$country = "";
		if ($address_id){
			$address = Mage::getModel('customer/address')->load($address_id);
			
			$city = $address->getData('city');
			$street = $address->getData('street');
			$postcode = $address->getData('postcode');
			$country = $address->getData('country_id');
		}
?>
							<tr>
								<td><?php echo $customer->getId(); ?></td>
								<td><?php echo $customer->getName(); ?></td>
								<td><?php echo $email; ?></td>	
								<td><a href="#" data-country="<?php echo $country; ?>" data-postcode="<?php echo $postcode; ?>" data-street="<?php echo $street; ?>" data-city="<?php echo $city; ?>" data-street="<?php echo $street; ?>" data-lastname="<?php echo $customer->getLastname(); ?>" data-firstname="<?php echo $customer->getFirstname(); ?>"data-email="<?php echo $email; ?>"data-name="<?php echo $customer->getName(); ?>" data-id="<?php echo $id; ?>" class="fa fa-user-plus userAdd"></a></td>								
							</tr>
<?php
	}
?>								
						</tbody>							
					</table>
				
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="button" class="btn btn-primary">OK</button>
					</div>
				</div>
			</div>
		</div>
		<!-- /users -->
        
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="js/bootstrap.min.js"></script>
        
        <!-- Moment.JS -->
        <script type="text/javascript" src="js/moment.min.js"></script>
        
        <!-- Datetime picker -->
        <script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
        
		<script>
			function isNumber(n) {
				return !isNaN(parseFloat(n)) && isFinite(n);
			}
			// Cleanup at start:
			$( document ).ready(function() {
				//alert('Loaded');
				$(".pqty").val('');
				$(".small").val($(".small option:first").val());
			});
			
			// Init datatimepicker:
			$(function () {
            	$('#datetimepicker1').datetimepicker();
            });			
			
			// Search for a user / products:
			$(".searchInput").keyup(function() {				
				if ('' != this.value) {
				    var reg = new RegExp(this.value, 'i');
					var stype = $(this).data("search");
					
				    $('#s' + stype).find('tr').each(function() {
				        var $me = $(this);
					    if (!$me.children('td').text().match(reg)) {
				            $me.hide();
				        } else {
				            $me.show();
				        }
				    });
				} else {
				    $('#sbody').find('tr').show();
				}
			});			
			
			// Add the user to the form
			$(".userAdd").click(function() {
				$("#name").val( $(this).data("name") );
				$("#email").val( $(this).data("email") );

				$("#bfirstname").val( $(this).data("firstname") );
				$("#blastname").val( $(this).data("lastname") );
				
				$("#bstreet").val( $(this).data("street") );
				$("#bcity").val( $(this).data("city") );
				$("#bpostcode").val( $(this).data("postcode") );
				$("#bcountry").val( $(this).data("country") );
				
				$('.users').modal('hide');
			});	
			
			// Billing address is same as shipping:
			$("#checkbilling").click(function() {
				if ($( this ).is(":checked")) {
					$("#sfirstname").val( $("#bfirstname").val() );
					$("#slastname").val( $("#blastname").val() );
					$("#sstreet").val( $("#bstreet").val() );
					$("#scity").val( $("#bcity").val() );
					$("#spostcode").val( $("#bpostcode").val() );
					$("#scountry").val( $("#bcountry").val() );					
				}
			});
			
			// Product selection:
			$(".productAdd").click(function() {
				var action = '<i class="fa fa-eraser fa-2x"></i>';
				var id = $(this).attr('id');
				id = id.substr(1);

				var qty = $("#qty" + id).val();
				if (!isNumber(qty) ) {
					// Not a number, so errror!
					$("#qty" + id).addClass("inputerror");
					$("#qty" + id).focus();
				} else {
				
					var productname = $("#name" + id).html();
					var sku = $("#sku" + id).html();
					var config = $("#label" + id).html();
					
					// Check if its a configurable product:
					if ($( "#select" + id ).length ){
						config = config + " - " +$("#select" + id + " option:selected").text();
					}
					
					// Append product to the main grid:
					$('#productlist').append('<tr><td>' + id + '</td><td>' + productname +'</td><td>' + sku + '</td><td>' + config + '</td><td>' + qty + '</td><td>' + action + '</td></td></tr>');
				}
				
			});
			
			// Input validation:
			$('input[name=qty]').keyup(function() {
				$(this).removeClass("inputerror");
				if(!isNumber($(this).val())) {
					$(this).addClass("inputerror");
				}
			});
			
			// Orderstatus change:
			$(".orderStatus").change(function() {
				var status = $(".orderStatus option:selected").val();

				switch(status) {
					case 'complete':
						$(".orderComplete").removeClass('hide');
						break;
						
				}
			});
			
			
			// Collapse billing and shipping adresses
			$('#collapseBilling').on('show.bs.collapse', function () {
				$("#collapseShipping").collapse('show');
			});

			$('#collapseBilling').on('hide.bs.collapse', function () {
				$("#collapseShipping").collapse('hide');
			});
			
		</script>
    </body>
</html>    

	