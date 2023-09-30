<?php
App::uses('View', 'View');
App::uses('SearchViewHelper', 'Search.View/Helper');

class SearchViewHelperTest extends CakeTestCase
{

    protected $SearchView;

    public function setUp()
    {
        parent::setUp();
        $this->SearchView = new SearchViewHelper(new View(), []);
    }

    public function tearDown()
    {
        unset($this->SearchView);
        parent::tearDown();
    }

    public function testRenderField()
    {
        $result = $this->SearchView->renderField('Advertisement.price', ['type' => 'text']);
        $this->assertContains('<div class="input text"><label for="AdvertisementPrice">Стоимость</label><input name="data[Advertisement][price]" data-alias="price" type="text" id="AdvertisementPrice"/></div>', $result);
    }

    public function testRenderRange()
    {
        $actual = $this->SearchView->renderRange('Advertisement.price', ['label' => 'Price', 'class' => 'price-field']);
        $this->assertContains('<label for="AdvertisementPriceFrom">Price</label>', $actual);
        $this->assertContains('<label for="AdvertisementPriceFrom" class="from-to">от</label>', $actual);
        $this->assertContains('<input name="data[Advertisement][price_from]" class="filter-from price-field" data-alias="price_from" type="text" id="AdvertisementPriceFrom"/>', $actual);
        $this->assertContains('<label for="AdvertisementPriceTo" class="from-to">до</label>', $actual);
        $this->assertContains('<input name="data[Advertisement][price_to]" class="filter-to price-field" data-alias="price_to" type="text" id="AdvertisementPriceTo"/>', $actual);

        App::uses('CustomSearchViewHelper', 'View/Helper');
        $this->SearchView = new CustomSearchViewHelper($this->SearchView->_View);
        $this->SearchView->_View->viewVars['filterPriceLimits'] = ['min' => 1234, 'max' => 24354454];

        $actual = $this->SearchView->renderRange('Advertisement.price', [
            'type' => 'range_value',
            'classes' => ['from' => 'JS-price-filter-lower', 'to' => 'JS-price-filter-upper'],
            'label' => __('Price'),
            'slider' => 1,
            'label_placeholder' => true,
            'tooltip' => false,
        ]);
        $this->assertContains('<label for="AdvertisementPriceFrom">Стоимость</label>', $actual);
        $this->assertContains('<input name="data[Advertisement][price_from]" class="filter-from  JS-price-filter-lower" min="1234" max="24354454" step="1" placeholder="от" data-alias="price_from" type="text" id="AdvertisementPriceFrom"/>', $actual);
        $this->assertContains('<input name="data[Advertisement][price_to]" class="filter-to  JS-price-filter-upper" min="1234" max="24354454" step="1" placeholder="до" data-alias="price_to" type="text" id="AdvertisementPriceTo"/>', $actual);
        $this->assertContains('<div data-field="price" data-min="1234" data-max="24354454" data-step="1" data-from="" data-to="" class="params-slider"></div>', $actual);
    }

    public function testRenderDate()
    {
        $result = $this->SearchView->renderDate('Advertisement.current_date', []);
        $this->assertContains('<input name="data[Advertisement][current_date]" type="date" id="AdvertisementCurrentDate"/>', $result);
    }

    public function testRender()
    {
        $result = $this->SearchView->render('Advertisement.price', ['type' => 'text']);
        $this->assertContains('<div class="input text"><label for="AdvertisementPrice">Стоимость</label><input name="data[Advertisement][price]" data-alias="price" type="text" id="AdvertisementPrice"/></div>', $result);
    }

    public function testGetRange()
    {
        $this->SearchView->_View->viewVars['filterPriceLimits'] = ['min' => 1234, 'max' => 24354454];

        $result = $this->SearchView->getRange(['alias' => 'price', 'class' => 'price-field']);
        $this->assertEquals(1234, $result['min']);
        $this->assertEquals(24354454, $result['max']);
    }

    public function testRenderInput()
    {
        $this->SearchView->Form->setEntity('Advertisement', true);
        $result = $this->SearchView->renderInput('area', []);
        $this->assertEquals('<div class="input text"><label for="AdvertisementArea">Площадь</label><input name="data[Advertisement][area]" type="text" id="AdvertisementArea"/></div>', $result);
    }

    public function testRenderSlider()
    {
        $this->SearchView->_View->viewVars['filterPriceLimits'] = ['min' => 0, 'max' => 50];
        $result = $this->SearchView->renderSlider('price', []);
        $this->assertEquals('<div data-field="price" data-min="0" data-max="50" data-step="1" data-from="" data-to="" class="params-slider"></div>', $result);
    }

    public function testRenderSmallerForm()
    {
        $this->SearchView->_View->set('modelName', 'Advertisement');
        $this->SearchView->_View->set('searchFields', [
            'Advertisement.category_id' => [
                'data-options' => 'categoriesTree',
            ]
        ]);
        $this->SearchView->_View->set('categoriesTree', [
            7 => 'Квартиры',
            35 => 'Комнаты',
            4 => 'Дома, дачи, коттеджи',
            2 => 'Коммерческая',
            43 => '--Офис',
            37 => 'Земельные участки',
            38 => 'Гаражи и машиноместа',
            39 => 'Новостройки'
        ]);
        $this->SearchView->_View->request->query['category_id'] = 7;

        $actual = $this->SearchView->renderSmallerForm();
        $this->assertContains('<div data-alias="category_id" title="category_id" class="small-filter-info">Квартиры', $actual);

        $this->SearchView->_View->request->query['category_id'] = [7, 35];

        $actual = $this->SearchView->renderSmallerForm();
        $this->assertContains('<div data-alias="category_id" title="category_id" class="small-filter-info">Квартиры', $actual);
        $this->assertContains('<div data-alias="category_id" title="category_id" class="small-filter-info">Комнаты', $actual);
    }

}
