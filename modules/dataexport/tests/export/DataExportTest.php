<?php defined('SYSPATH') or die('No direct access allowed!');

class DataExportTest extends Kohana_UnitTest_TestCase
{
    public function testFactoryForCSVDriver()
    {
        $data_exporter = DataExport::Factory(array('driver' => 'CSV'), array());

        $this->assertInstanceOf('DataExport_Driver_CSV', $data_exporter, 'DataExport::Factory did not return correct driver instance');
    }

    /**
     * @expectedException DataExport_Exception_DriverNotFound
     */
    public function testFactoryForUndefinedDriver()
    {
        DataExport::Factory(array('driver' => '_undefined_driver'), array());
    }

    /**
     * @expectedException DataExport_Exception_InvalidConfiguration
     */
    public function testFactoryForInvalidConfiguration()
    {
        DataExport::Factory(array('no_driver_type_defined' => 'whatever'), array());
    }
}