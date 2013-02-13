<?php
    protected $_mockery_ignoreVerification = true;

    public function __construct()
    {
        $this->_mockery_ignoreVerification = false;
        $associatedRealObject = \Mockery::fetchMock(__CLASS__);
        $directors = $associatedRealObject->mockery_getExpectations();
        foreach ($directors as $method=>$director) {
            $expectations = $director->getExpectations();
            // get the director method needed
            $existingDirector = $this->mockery_getExpectationsFor($method);
            if (!$existingDirector) {
                $existingDirector = new \Mockery\ExpectationDirector($method, $this);
                $this->mockery_setExpectationsFor($method, $existingDirector);
            }
            foreach ($expectations as $expectation) {
                $clonedExpectation = clone $expectation;
                $existingDirector->addExpectation($clonedExpectation);
            }
        }
        \Mockery::getContainer()->rememberMock($this);
    }
