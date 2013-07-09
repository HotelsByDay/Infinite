<?php defined('SYSPATH') or die('No direct access allowed!');

class DataExportFileStorageTest extends Kohana_UnitTest_TestCase
{
    /**
     * @expectedException DataExport_Exception_StorageAlreadyInitialized
     */
    public function testMultipleInitCall()
    {
        $object = new DataExport_FileStorage();

        $object->Init('test_file_name');
        $object->Init(NULL);
    }

    /**
     * @expectedException Exception
     */
    public function testInitCallWithEmptyFilename()
    {
        $object = new DataExport_FileStorage();

        $object->Init(NULL);
    }

    public function testInitIsChainable()
    {
        $object = new DataExport_FileStorage();

        $Init_retval = $object->Init('whatever_filename');

        $this->assertInstanceOf('DataExport_FileStorage', $Init_retval);
    }

    public function testCorrectInitCall()
    {
        $filename = 'file';

        $object = new DataExport_FileStorage();

        $created_filepath = $object->Init($filename)
            ->getFilePath();

        $created_filenane = basename($created_filepath);

        $this->assertEquals($filename, $created_filenane, 'The name of the created file for export does not match the name that was passed to the Init() call.');
    }

    public function testRandomDirGenerating()
    {
        $generated_filenames = array();

        for ($i = 0 ; $i < 100 ; $i++)
        {
            $object = new DataExport_FileStorage();

            $filepath = $object->Init('filename')
                ->getFilePath();

            $this->assertArrayNotHasKey($filepath, $generated_filenames, "Unable to generate enough random filenames - see test implementation for more details.");

            $this->assertTrue(file_exists($filepath), 'File "'.$filepath.'" created by DataExport_FileStorage does not exists.');

            $object->fileRemove();

            $this->assertTrue( ! file_exists($filepath), 'File "'.$filepath.'" was not removed by DataExport_FileStorage.');

            $generated_filenames[$filepath] = $object;
        }
    }

    /**
     * @expectedException DataExport_Exception_FilenameEmpty
     */
    public function testFileOpenWithoutInitialization()
    {
        $object = new DataExport_FileStorage();

        $object->fileOpen();
    }

    /**
     * @expectedException Exception
     */
    public function testFileAppendWithoutInitialization()
    {
        $object = new DataExport_FileStorage();

        $object->fileAppend('');
    }
}