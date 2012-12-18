<?php defined('SYSPATH') or die('No direct access allowed!');

class DataExportCSVDriverTest extends Kohana_UnitTest_TestCase
{
    /**
     * @expectedException ErrorException
     * @dataProvider dataProviderInvalidFactoryArguments
     */
    public function testInvalidConstructorArguments()
    {
        new DataExport_Driver_CSV(NULL, NULL, NULL);
    }

    /**
     * @dataProvider dataProviderInvalidFilenameGeneratorConfiguration
     * @expectedException DataExport_Exception_InvalidConfiguration
     */
    public function testInvalidFilenameGeneratorConfiguration(array $config)
    {
        $data_exporter = new DataExport_Driver_CSV($config, array(), new DataExport_FileStorage());

        $data_exporter->getUserFileName();
    }

    /**
     * @dataProvider dataProviderValidFilenameGeneratorConfiguration
     */
    public function testValidFilenameGeneratorConfiguration(array $config, $expected_filename)
    {
        $data_exporter = new DataExport_Driver_CSV($config, array(), new DataExport_FileStorage());

        //$filename = $data_exporter->getUserFileName();

        //$this->assertEquals($expected_filename, $filename, "The returned filename \"$filename\" does not match the expected filename \"$expected_filename\".");
    }

    /**
     * @dataProvider dataProviderGetFilePath
     */
    public function testGetFilePath(array $config)
    {
        $expected_filename = 'test_file_name.csv';

        $data_exporter = new DataExport_Driver_CSV($config, array(), Testable_DataExport_FileStorage_Stub::stub_factory($expected_filename));

        $filepath = $data_exporter->getFilePath();

        $this->assertEquals($expected_filename, basename($filename), "The returned source filename \"$filename\" does not match the expected source filename \"$expected_filename\".");
    }

    public function testGenerateExportIsChaniable()
    {
        $data_exporter = new DataExport_Driver_CSV($config, array(), new DataExport_FileStorage());

        $generateExport_retval = $data_exporter->generateExport();

        $this->assertInstanceOf('DataExport_Drive_CSV', $generateExport_retval, 'Method generateExport does not chainable.');
    }

    /**
     * @dataProvider dataProviderInvalidHeaderLineGeneratorConfiguration
     * @expectedException DataExport_Exception_InvalidConfiguration
     */
    public function testInvalidHeaderLineGeneratorConfiguration(array $config)
    {
        $data_exporter = new DataExport_Driver_CSV($config, array(), new DataExport_FileStorage());

        $data_exporter->generateExport();
    }

    /**
     * @dataProvider dataProviderValidHeaderLineGeneratorConfiguration
     */
    public function testValidHeaderLineGeneratorConfiguration(array $config, $expected_header_line)
    {
        $file_storage = Testable_DataExport_FileStorage_Stub::stub_factory('whatever_testing_filename');

        $data_exporter = new DataExport_Driver_CSV($config, array(), $file_storage);

        $data_exporter->generateExport();

        $header_line = $file_storage->getContent();

        $this->assertEquals($expected_header_line, $header_line, "The header line \"$header_line\" does not match the expected value \"$expected_header_line\".");
    }

    /**
     * @dataProvider dataProviderInvalidLineDataGeneratorConfiguration
     * @expectedException DataExport_Exception_InvalidConfiguration
     */
    public function testInvalidDataLineGeneratorConfiguration(array $config)
    {
        $file_storage = Testable_DataExport_FileStorage_Stub::stub_factory('whatever_testing_filename');

        $data_exporter = new Testable_DataExport_Driver_CSV($config, array(new stdClass()), $file_storage);

        $data_exporter->generateExport();
    }

    /**
     * @dataProvider dataProviderValidLineDataGeneratorConfiguration
     */
    public function testValidDataLineGeneratorConfiguration(array $config)
    {
        $file_storage = Testable_DataExport_FileStorage_Stub::stub_factory('whatever_testing_filename');

        $data_exporter = new Testable_DataExport_Driver_CSV($config, array(new stdClass()), $file_storage);

        $data_exporter->generateExport();
    }

    /**
     * @dataProvider dataProviderInvalidCSVLNConfiguration
     * @expectedException DataExport_Exception_InvalidConfiguration
     */
    public function testInvalidCSVLNConfiguration(array $config)
    {
        $file_storage = Testable_DataExport_FileStorage_Stub::stub_factory('whatever_testing_filename');

        $data_exporter = new Testable_DataExport_Driver_CSV($config, array(new stdClass()), $file_storage);

        $data_exporter->generateExport();
    }

    public function dataProviderGetFilePath()
    {
        return array(
            array(
                array(
                    'filename_generator' => function(){
                        return 'test';
                    }
                ),
                'test',
            ),
        );
    }

    public function dataProviderInvalidFactoryArguments()
    {
        return array(
            array(NULL, NULL, NULL),
            array(array(), array(), array()),
        );
    }

    public function dataProviderInvalidFilenameGeneratorConfiguration()
    {
        return array(
            array(
                array(
                    'filename_generator' => 'string'
                )
            ),
            array(
                array(
                    'filename_generator' => 123
                )
            ),
            array(
                array(
                    'filename_generator' => NULL
                ),
            ),
            array(
                array(
                    '-no-filename-generator-defined-' => NULL,
                )
            )
        );
    }

    public function dataProviderValidFilenameGeneratorConfiguration()
    {
        return array(
            array(
                array(
                    'filename_generator' => function(){
                        return 'test';
                    }
                ),
                'test',
            ),
        );
    }

    public function dataProviderInvalidHeaderLineGeneratorConfiguration()
    {
        return array(
            array(
                array(
                    'header_generator' => 'string'
                )
            ),
            array(
                array(
                    'header_generator' => 123
                )
            ),
            array(
                array(
                    'header_generator' => ''
                )
            ),

        );
    }

    public function dataProviderValidHeaderLineGeneratorConfiguration()
    {
        return array(
            array(
                array(
                    'header_generator' => NULL,
                ),
                "",
            ),
            array(
                array(
                    '-no-header_generator-defined' => NULL,
                ),
                "",
            ),
            array(
                array(
                    'header_generator' => function(){
                        return array(
                            'col1',
                            'col2'
                        );
                    },
                    'nl' => "\n",
                    'delimiter' => ';',
                ),
                "col1;col2\n",
            ),
            array(
                array(
                    'header_generator' => function(){
                        return array(
                            'co;l1',
                            'c;ol2'
                        );
                    },
                    'nl' => "\n",
                    'delimiter' => ';',
                ),
                "co\;l1;c\;ol2\n",
            ),
        );
    }

    public function dataProviderInvalidLineDataGeneratorConfiguration()
    {
        return array(
            array(
                array(
                    'line_generator' => 'string'
                )
            ),
            array(
                array(
                    'line_generator' => 123
                )
            ),
            array(
                array(
                    'line_generator' => NULL
                )
            ),
            array(
                array(
                    //empty
                )
            ),
        );
    }

    public function dataProviderValidLineDataGeneratorConfiguration()
    {
        return array(
            array(
                array(
                    'line_generator' => function(){
                        return array();
                    }
                )
            ),
        );
    }

    public function dataProviderInvalidCSVLNConfiguration()
    {
        return array(
            array(
                array('nl' => '')
            ),
            array(
                array('nl' => '123')
            ),
            array(
                array('nl' => '     ')
            ),
        );
    }

    public function dataProviderTestCSVLNDefaultValueConfiguration()
    {
        return array(
            array(
                array('nl' => NULL)
            ),
            array(
                array()
            ),
        );
    }

    public function dataProviderTestGetDataLine()
    {
        return array(
            array(
                array(
                    'nl' => "\r\n",
                    'delimiter' => ';',
                    'line_generator' => function($model)
                    {
                        return array(
                            $model->attr1,
                            $model->attr2,
                        );
                    },
                ),
                (object)array('attr1' => 'ahoj', 'attr2' => 'test;test'),
                //expected output from the line generator
                "ahoj;test\;test\r\n",
            ),
            array(
                array(
                    'nl' => "\r",
                    'delimiter' => '-',
                    'line_generator' => function($model)
                    {
                        return array(
                            $model->attr1,
                            $model->attr2,
                        );
                    },
                ),
                (object)array('attr1' => 'ahoj', 'attr2' => 'test;test'),
                //expected output from the line generator
                "ahoj-test;test\r",
            ),
            array(
                array(
                    'nl' => "\n",
                    'delimiter' => '-',
                    'line_generator' => function($model)
                    {
                        return array(
                        );
                    },
                ),
                (object)array(),
                //expected output from the line generator
                "\n",
            ),
            array(
                array(
                    'nl' => "\n",
                    'delimiter' => "\t",
                    'line_generator' => function($model)
                    {
                        return array(
                            $model->attr1,
                            $model->attr2,
                        );
                    },
                ),
                (object)array('attr1' => '-', 'attr2' => '-'),
                //expected output from the line generator
                "-\t-\n",
            ),
        );
    }

    public function dataProviderTestGetHeaderLineFromCallable()
    {
        return array(
            array(
                array(
                    'nl' => "\r\n",
                    'delimiter' => ';',
                    'header_generator' => function()
                    {
                        return array(
                            'ahoj',
                            'test;test',
                        );
                    },
                ),
                //expected output from the line generator
                "ahoj;test\;test\r\n",
            ),
            array(
                array(
                    'nl' => "\r",
                    'delimiter' => '-',
                    'header_generator' => function()
                    {
                        return array(
                            'ahoj',
                            'test;test',
                        );
                    },
                ),
                //expected output from the line generator
                "ahoj-test;test\r",
            ),
            array(
                array(
                    'nl' => "\n",
                    'delimiter' => '-',
                    'header_generator' => function()
                    {
                        return array(
                        );
                    },
                ),
                //expected output from the line generator
                "\n",
            ),
            array(
                array(
                    'nl' => "\n",
                    'delimiter' => "\t",
                    'header_generator' => function()
                    {
                        return array(
                            '-',
                            '-',
                        );
                    },
                ),
                //expected output from the line generator
                "-\t-\n",
            ),
        );
    }
}