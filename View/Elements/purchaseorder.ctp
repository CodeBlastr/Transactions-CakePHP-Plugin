<!--div id="purchaseOrder" class="paymentOption"-->
	<?php echo $this->Form->input('Transaction.po_number', array('label' => 'PO Number', 'class' => 'purchaseOrder')); ?>
	<?php #echo $this->Html->link('<i class="icon-print"></i> Print Invoice', array('action' => 'pdfInvoice', $this->request->data['Transaction']['id']), array('class' => 'btn btn-info', 'escape' => false)); ?>
	<button class="btn-info btn purchaseOrder pdfInvoice" type="submit" name="printInvoice" value="printInvoice"><i class="icon-print"></i> Print Invoice</button>
<!--/div-->

<script type="text/javascript">
	$(document).ready(function() {

		document.changeToPurchaseOrder = function changeToPurchaseOrder() {
			$("#creditCardInfo, #echeckInfo").children().removeClass('required');
			//$("#TransactionPoNumber").addClass('required');
			$('.paysimpleCc').parent().parent().hide();
			$('.paysimpleCheck').parent().parent().hide();
			$('.purchaseOrder').parent().parent().show();
			$('.purchaseOrder.pdfInvoice').show();
			$('.payByCheck.pdfInvoice').hide();
		};

	});
</script>
