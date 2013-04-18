<?php
App::uses('Controller', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('HttpSocket', 'Network/Http');
App::uses('ComponentCollection', 'Controller');
App::uses('PaysimpleComponent', 'Transactions.Controller/Component');

// A fake controller to test against
class TestPaysimpleController extends Controller {
}

class PaysimpleComponentTest extends CakeTestCase {
    public $PaysimpleComponent = null;
    public $Controller = null;
	
    public function setUp() {
        parent::setUp();
		
		define('__TRANSACTIONS_PAYSIMPLE', serialize(parse_ini_string('environment = sandbox
apiUsername = APIUser666
sharedSecret = asdfasdf')));
		
        // Setup our component and fake test controller
        $Collection = new ComponentCollection();
        $this->PaysimpleComponent = new PaysimpleComponent($Collection);

        $CakeRequest = new CakeRequest();
        $CakeResponse = new CakeResponse();
        $this->Controller = new TestPaysimpleController($CakeRequest, $CakeResponse);
        $this->PaysimpleComponent->startup($this->Controller);
    }

	/**
	 * Ensure that our getIssuer() is working properly
	 */
    public function testgetIssuer() {
		$issuerCode = $this->PaysimpleComponent->getIssuer('4');
        $this->assertEquals(false, $issuerCode);
		
		$issuerCode = $this->PaysimpleComponent->getIssuer('4111111111111111');
        $this->assertEquals(12, $issuerCode);
		
        $issuerCode = $this->PaysimpleComponent->getIssuer('5555-3440-6432-4523');
        $this->assertEquals(13, $issuerCode);

        $issuerCode = $this->PaysimpleComponent->getIssuer('5589624064244571');
        $this->assertEquals(13, $issuerCode);
    }

    public function tearDown() {
        parent::tearDown();
        // Clean up after we're done
        unset($this->PaysimpleComponent);
        unset($this->Controller);
    }
	
}