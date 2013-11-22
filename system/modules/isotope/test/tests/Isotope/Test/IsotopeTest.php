<?php

namespace Isotope\Test;

class IsotopeTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertWeight()
    {
        $this->assertEquals(\Isotope\Isotope::convertWeight(2, 'kg', 'g'), 2000);
        $this->assertEquals(\Isotope\Isotope::convertWeight(2, 'g', 'mg'), 2000);
        $this->assertEquals(\Isotope\Isotope::convertWeight(2, 'kg', 'mg'), 2000000);
    }

    public function testRegexpPrice()
    {
        \Input::setPost('test_rgxp_price', 'foobar');
        $objWidget = new \TextField(array('name'=>'test_rgxp_price'));
        $objWidget->rgxp = 'price';
        $objWidget->validate();

        $this->assertTrue($objWidget->hasErrors());
        unset($objWidget);

        \Input::setPost('test_rgxp_price', '20.00');
        $objWidget = new \TextField(array('name'=>'test_rgxp_price'));
        $objWidget->rgxp = 'price';
        $objWidget->validate();

        $this->assertFalse($objWidget->hasErrors());
        unset($objWidget);

        \Input::setPost('test_rgxp_price', '20');
        $objWidget = new \TextField(array('name'=>'test_rgxp_price'));
        $objWidget->rgxp = 'price';
        $objWidget->validate();

        $this->assertFalse($objWidget->hasErrors());
        unset($objWidget);

        \Input::setPost('test_rgxp_price', '-20');
        $objWidget = new \TextField(array('name'=>'test_rgxp_price'));
        $objWidget->rgxp = 'price';
        $objWidget->validate();

        $this->assertFalse($objWidget->hasErrors());
        unset($objWidget);

        \Input::setPost('test_rgxp_price', '20.-');
        $objWidget = new \TextField(array('name'=>'test_rgxp_price'));
        $objWidget->rgxp = 'price';
        $objWidget->validate();

        $this->assertTrue($objWidget->hasErrors());
        unset($objWidget);
    }
}