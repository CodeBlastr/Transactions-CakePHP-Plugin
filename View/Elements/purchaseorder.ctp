<div id="purchaseOrder" class="paymentOption">
	<?php echo $this->Form->input('po_number', array('label' => 'PO Number')); ?>
	<?php echo $this->Html->link('<i class="icon-print"></i> Print Invoice', array(), array('class' => 'btn', 'escape' => false)); ?>
</div>

<script type="text/javascript">
	$(document).ready(function() {

		document.changeToPurchaseOrder = function changeToPurchaseOrder() {
			$("#creditCardInfo, #echeckInfo").children().removeClass('required');
			$('.paysimpleCc').parent().parent().hide();
			$('.paysimpleCheck').parent().parent().hide();
			$('#purchaseOrder').show();
		};

	});
</script>
