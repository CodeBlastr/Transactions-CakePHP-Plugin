<div id="purchaseOrder" class="paymentOption">
	<?php echo $this->Form->input('Transaction.po_number', array('label' => 'PO Number')); ?>
	<?php echo $this->Html->link('<i class="icon-print"></i> Print Invoice', array('action' => 'pdfInvoice', $this->request->data['Transaction']['id']), array('class' => 'btn', 'escape' => false)); ?>
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
