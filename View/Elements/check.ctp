<!--div id="payByCheck" class="paymentOption"-->
<?php //echo $this->Form->input('Transaction.po_number', array('label' => 'PO Number')); ?>

<?php
//echo $this->Html->link(
//		'<i class="icon-print"></i> Print Invoice',
//		array(
//			'action' => 'pdfInvoice',
//			$this->request->data['Transaction']['id']
//			),
//		array(
//			'class' => 'btn btn-info payByCheck',
//			'escape' => false
//			)
//		);
?>

<button class="btn-info btn payByCheck pdfInvoice" type="submit" name="printInvoice" value="printInvoice"><i class="icon-print"></i> Print Invoice</button>

<!--/div-->

<script type="text/javascript">
	$(document).ready(function() {

		document.changeToCheck = function changeToCheck() {
			$("#creditCardInfo, #echeckInfo").children().removeClass('required');
			$('.paysimpleCc').parent().parent().hide();
			$('.paysimpleCheck').parent().parent().hide();
			$('.purchaseOrder').parent().parent().show();
			$('.purchaseOrder.pdfInvoice').hide();
			$('.payByCheck.pdfInvoice').show();
		};

	});
</script>
