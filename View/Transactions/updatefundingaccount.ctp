<?php
/**
*@var $this View
**/
$merchantId = $this->Session->read('Auth.User.merchant_account');

?>
<?php if(!empty($merchantId)): ?>
<?php echo $this->Form->create('Transaction'); ?>
    <?php echo $this->Form->create('Transaction'); ?>
    <h4>Edit Funding Account</h4>

    <table class="table table-hover">
        <caption style="text-align: left;font-weight: bold">Bank information</caption>

        <tbody>
        <tr>
            <td class="funding-table-title">Account ID</td>
            <td><?php echo $this->Session->read('Auth.User.merchant_account'); ?></td>
        </tr>
        <tr>
            <td class="funding-table-title">Bank Account#</td>
            <td><?php echo $this->Form->input('merchant.funding.accountNumber', array('label' => false, 'div' => false, 'required' => 'required', 'placeholder' => 'Account Number', 'autocomplete' => 'off', 'class' => 'form-control')); ?></td>
        </tr>
        <tr>
            <td class="funding-table-title">Bank Routing#</td>
            <td><?php echo $this->Form->input('merchant.funding.routingNumber', array('label' => false, 'div' => false, 'required' => 'required', 'placeholder' => 'Routing Number', 'autocomplete' => 'off', 'class' => 'form-control')); ?></td>
        </tr>
        </tbody>
    </table>

    <table class="table table-hover">
        <caption style="text-align: left;font-weight: bold">Personal Information</caption>

        <tbody>
        <tr>
            <td class="funding-table-title">
                First Name
            </td>
            <td>
                <?php echo $this->Form->input('merchant.individual.firstName', array('label' => false, 'div' => false, 'required' => 'required', 'placeholder' => 'First Name', 'autocomplete' => 'off', 'class' => 'input-lg form-control')); ?>
            </td>
        </tr>
        <tr>
            <td class="funding-table-title">
                Last Name
            </td>
            <td>
                <?php echo $this->Form->input('merchant.individual.lastName', array('label' => false, 'div' => false, 'required' => 'required', 'placeholder' => 'Last Name', 'autocomplete' => 'off', 'class' => 'form-control')); ?>
            </td>
        </tr>
        <tr>
            <td class="funding-table-title">Email</td>
            <td>
                <?php echo $this->Form->input('merchant.individual.email', array('label' => false, 'div' => false, 'type' => 'email', 'required' => 'required', 'placeholder' => 'Email', 'autocomplete' => 'off', 'class' => 'form-control')); ?>
            </td>
        </tr>
        <tr>
            <td class="funding-table-title">Phone</td>
            <td>
                <?php echo $this->Form->input('merchant.individual.phone', array('label' => false, 'div' => false, 'placeholder' => 'phone number(optional)', 'autocomplete' => 'off', 'class' => 'form-control')); ?>
            </td>
        </tr>
        <tr>
            <td class="funding-table-title">Date of Birth</td>
            <td>
                <?php echo $this->Form->input('merchant.individual.dateOfBirth', array('label' => false, 'div' => false, 'required' => 'required', 'placeholder' => 'Format 1980-01-01', 'autocomplete' => 'off', 'class' => 'form-control')); ?>
            </td>
        </tr>
        <tr>
            <td class="funding-table-title">SSN</td>
            <td>
                <?php echo $this->Form->input('merchant.individual.ssn', array('label' => false, 'div' => false, 'placeholder' => 'SSN(optional)', 'autocomplete' => 'off', 'class' => 'form-control')); ?>
            </td>
        </tr>
        </tbody>
    </table>

    <table class="table table-hover">
        <caption style="text-align: left;font-weight: bold">Home Address(All Fields Required)</caption>

        <tbody>
        <tr>
            <td class="funding-table-title">
                Street Address
            </td>
            <td>
                <?php echo $this->Form->input('merchant.individual.address.streetAddress', array('label' => false, 'div' => false, 'required' => 'required', 'placeholder' => 'Street Address', 'autocomplete' => 'off', 'class' => 'form-control')); ?>
            </td>
        </tr>
        <tr>
            <td class="funding-table-title">
                City
            </td>
            <td>
                <?php echo $this->Form->input('merchant.individual.address.locality', array('label' => false, 'div' => false, 'required' => 'required', 'placeholder' => 'City', 'autocomplete' => 'off', 'class' => 'form-control')); ?>
            </td>
        </tr>
        <tr>
            <td class="funding-table-title">State</td>
            <td>
                <?php echo $this->Form->input('merchant.individual.address.region', array('label' => false, 'div' => false, 'options' => states(), 'empty' => 'Choose a state'));?>
            </td>
        </tr>
        <tr>
            <td class="funding-table-title">Zip Code</td>
            <td>
                <?php echo $this->Form->input('merchant.individual.address.postalCode', array('label' => false, 'div' => false, 'placeholder' => 'Zip Code', 'autocomplete' => 'off', 'class' => 'form-control')); ?>
            </td>
        </tr>

        </tbody>
    </table>


    <table class="table table-hover">
        <caption style="text-align: left;font-weight: bold">Business Information(Optional)</caption>

        <tbody>
        <tr>
            <td class="funding-table-title">
                Legal Name
            </td>
            <td>
                <?php echo $this->Form->input('merchant.business.legalName',array('label' => false, 'div' => false,'placeholder'=>'Legal Name','autocomplete'=>'off','class'=>'form-control'));?>
            </td>
        </tr>
        <tr>
            <td class="funding-table-title">
                Tax ID
            </td>
            <td>
                <?php echo $this->Form->input('merchant.business.taxId', array('label' => false, 'div' => false, 'placeholder' => 'Tax ID in format 98-7654321', 'autocomplete' => 'off', 'class' => 'form-control')); ?>
            </td>
        </tr>
        <tr>
            <td class="funding-table-title">DBA Name</td>
            <td>
                <?php echo $this->Form->input('merchant.business.dbaName', array('label' => false, 'div' => false, 'placeholder' => 'DBA Name optional', 'autocomplete' => 'off', 'class' => 'form-control')); ?>
            </td>
        </tr>
        <tr>
            <td class="funding-table-title">Street Address</td>
            <td>
                <?php echo $this->Form->input('merchant.business.address.streetAddress', array('label' => false, 'div' => false, 'placeholder' => 'Street Address', 'autocomplete' => 'off', 'class' => 'form-control')); ?>
            </td>
        </tr>
        <tr>
            <td class="funding-table-title">City</td>
            <td>
                <?php echo $this->Form->input('merchant.business.address.locality', array('label' => false, 'div' => false, 'placeholder' => 'City', 'autocomplete' => 'off', 'class' => 'form-control')); ?>
            </td>
        </tr>
        <tr>
            <td class="funding-table-title">State</td>
            <td>
                <?php echo $this->Form->input('merchant.business.address.region', array('label' => false, 'div' => false, 'options' => states(), 'empty' => 'Choose a state'));?>
            </td>
        </tr>
        <tr>
            <td class="funding-table-title">Zip Code</td>
            <td>
                <?php echo $this->Form->input('merchant.business.address.postalCode', array('label' => false, 'div' => false, 'placeholder' => 'Zip Code', 'autocomplete' => 'off', 'class' => 'form-control')); ?>
            </td>
        </tr>
        </tbody>
    </table>


    <button type="submit" class="btn btn-primary">Submit</button>
    <a href="/users/users/my" class="btn btn-default">Cancel</a>
    <?php echo $this->Form->end(); ?>

    <style type="text/css">
        .funding-table-title {
            width: 100px;
            font-weight: bold;
            color: #000000;
        }
    </style>
<?php else: ?>
    <div>
        <h2>You don't have any account, please <?php echo $this->Html->link('Add Funding Account', array('plugin' => 'transactions', 'controller' => 'transactions', 'action' => 'addfundingaccount')); ?></h2>
    </div>

<?php endif;?>