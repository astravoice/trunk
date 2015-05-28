
<? extend('master.php') ?>
<?php error_reporting(E_ERROR);?>
<? startblock('extra_head') ?>

<script type="text/javascript">


$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

    $(document).ready(function() {

	
	$('#datetimepicker9').datetimepicker({
                format: 'MM/YY'
            });

        function updateBalance(amount){
		$('#balance').trigger("balance::update", amount);
	}

	$('#btn-pay').on('click', function(e){
	  if($("#amount").val() > 0){
                console.log($(this));
		e.preventDefault();

		
		var data = $('#payment_form').serializeObject();
	        console.log(data);
        	$.ajax({
                	type:'post',
                	url:'pay',
                	data:JSON.stringify(data),
                	success:function(res){
				updateBalance(res.response.message);
				console.log(res);
			},
                	error:function(res, e){
				console.log(res);

                },
        	}).always(function(){
		
		});
		}else{
		
		};	
		return false;
	});
    });
</script>

<?php endblock() ?>
<?php startblock('page-title') ?>
<?=$page_title?>
<br/>
<?php endblock() ?>
<?php startblock('content')?>


			<?php if (isset($validation_errors)) echo $validation_errors; ?> 

<!-- Credit card form -->
<div class="container">
    <div class="row">
        <div class="col-xs-12 col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><img class="pull-right" src="http://i76.imgup.net/accepted_c22e0.png">Payment Details</h3>
                </div>
                <div class="panel-body">
                    <form action="/user/authorize/pay" method="post" role="form" id="payment_form">
                        <div class="row">
			    <div class="col-xs-12">
                                <div class="form-group">
					                        <label>RECHARGE AMOUNT <?= $from_currency?></label>
                        <div class="input-group">
  <span class="input-group-addon">$</span>
  <input type="text" id="amount" name="amount" class="form-control" placeholder="15" aria-label="Amount (to the nearest dollar)">
  <span class="input-group-addon">.00</span>
</div>
</div>
</div>
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label for="cc">CARD NUMBER</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="cc" placeholder="Valid Card Number" required autofocus data-stripe="number" />
                                        <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                    </div>
                                </div>                            
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-7 col-md-7">
                                <div class="form-group">
                                    <label for="xm">EXPIRATION DATE</label>
                                    <div class="col-xs-6 col-lg-6 pl-ziro">
                                        <input type="text" class="form-control" name="xm" placeholder="MM" required data-stripe="exp_month" />
                                    </div>
                                    <div class="col-xs-6 col-lg-6 pl-ziro">
                                        <input type="text" class="form-control" name="xy" placeholder="YY" required data-stripe="exp_year" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-5 col-md-5 pull-right">
                                <div class="form-group">
                                    <label for="sc">CV CODE</label>
                                    <input type="password" class="form-control" name="cvCode" placeholder="CV" required data-stripe="cvc" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label for="coupon">COUPON CODE</label>
                                    <input type="text" class="form-control" name="couponCode" />
                                </div>
                            </div>                        
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
				<input type="hidden" readonly name="item_number" value="<?=$accountid?>">
                  <input type="hidden" readonly name="PHPSESSID" value="<?=session_id();?>">
                  <input type="hidden" readonly name="currency_code" value="USD">
                  <input type="hidden" readonly name="custom" id='custom' value="">


                                <button class="btn btn-success btn-lg btn-block ladda-button" data-style="expand-left" type="submit" id="btn-pay" ><span class="ladda-label">Recharge Account</span></button>
                            </div>
                        </div>
                        <div class="row" style="display:none;">
                            <div class="col-xs-12">
                                <p class="payment-errors"></p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<? endblock() ?>
<? startblock('sidebar') ?>
<? endblock() ?>
<? end_extend() ?> 
