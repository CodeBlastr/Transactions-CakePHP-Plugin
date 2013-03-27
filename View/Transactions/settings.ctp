
<p>The email that will be sent to successful purchasers.</p>
<p>Available tags : </p>
<ul>
	<li>{element: transactionItems} -> a table of purchased items</li>
</ul>
<?php
echo $this->Form->create('Setting', array('url' => array('plugin' => null, 'controller' => 'settings', 'action' => 'add')));
echo $this->Form->input('Override.redirect', array('type' => 'hidden', 'value' => '/admin/products/products/dashboard'));
echo $this->Form->input('Setting.type', array('type' => 'hidden', 'value' => 'Transactions'));
echo $this->Form->input('Setting.name', array('type' => 'hidden', 'value' => 'RECEIPT_EMAIL'));
echo $this->Form->input('Setting.value.subject', array('label' => 'Subject'));
echo $this->Form->input('Setting.value.body', array('type' => 'richtext', 'label' => false));
echo $this->Form->end('Save'); ?>

<?php /*
<h3>Minimum settings to get a store working.</h3>
__TRANSACTIONS_DEFAULT_PAYMENT = PAYSIMPLE <br />
<br />
[__TRANSACTIONS_ENABLE_PAYMENT_OPTIONS]<br />
PAYSIMPLE.CC = "Credit Card"<br />
PAYSIMPLE.CHECK = "Echeck"<br />
<br />
<br />
[__TRANSACTIONS_PAYSIMPLE]<br />
environment = sandbox<br />
apiUsername = APIUser66932<br />
sharedSecret = WEg7u5wrn0213dJ86myXGoHlQApJcnLfA5uKN0e1hUhLbnxGaki6EI4KDWYQ1mFrXuGX0EeXRJ4M3HNBPCq6HNfjwBpPncMbWp2GjplQeIMAsQL0D3eGmM8IJVkGRUm0<br />
<br />
<br />
AND MAKE SURE THAT THE CONNECTIONS PLUGIN IS INSTALLED IF PAYSIMPLE IS INSTALLED<br /> */ ?>