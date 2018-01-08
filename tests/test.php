<?php
/**
 * This script is used to test whether phpwget functions as required.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */
namespace PhpWget;

class Test {
    /**
     * PhpWget script file path
     *
     * @var string $testFilePath
     */
    private $testFilePath = '../phpwget.php';

    /**
     * Test script create temporary file path
     *
     * @var string $testFilePath
     */
    private $tempFilePath = [
        1 => 'index.html',
        2 => 'index.html',
        3 => 'v0.1.tar.gz',
        4 => [
            'PhpWget-0.1',
            'tar.v0.1'
        ],
    ];

    /**
     * @var array $errorMassages
     */
    private $errorMassages = [
        1 => "[Warning] This script must be run in cli mode.\n",
        2 => "[Warning] You did not load curl extension, PhpWget will not work.\n",
        3 => "[Warning] PhpWget script does not exist or the working path is wrong, please check the script exists and make sure to call this script in the subordinate directory of the script.\n",
        4 => "[Warning] 'exec' function has been disabled, please enable it. This script needs this function.\n",
        'error' => [
            1 => "[Error] PhpWget can not download file from Internet.\n",
            2 => "[Error] PhpWget can not correctly download files containing multi-level domain URL.\n",
            3 => "[Error] PhpWget can not follow the redirect to download.\n",
            4 => "[Error] PhpWget can not extract the archive after download file.\n"
        ],
        'notice' => [
            1 => "[Notice] PhpWget can not remove temporary file.\n",
            2 => "[Notice] PhpWget can not remove temporary directory.\n"
        ]
    ];

    private $passMassage = [
        1 => "[Test 1/4 Pass] Test whether PhpWget can download files from Internet\n",
        2 => "[Test 2/4 Pass] Test whether PhpWget can correctly download files containing multi-level domain URL\n",
        3 => "[Test 3/4 Pass] Test whether PhpWget can follow the redirect to download file\n",
        4 => "[Test 4/4 Pass] Test whether PhpWget can extract the archive after download\n",
        'final' => "\nPhpWget feature is OK.\n"
    ];

    public function __construct() {
        $this->checkPHPEnvironment();
        $this->checkFileExist();
    }

    /**
     * Check if the server meets the requirements
     */
    private function checkPHPEnvironment() {
        // Check if this script is running in cli mode
        if ( php_sapi_name() !== 'cli' ) {
            echo $this->errorMassages[1];
            die ( 1 );
        }
        if ( !extension_loaded( 'curl' ) ) {
            echo $this->errorMassages[2];
            die ( 1 );
        }
        if ( !function_exists( 'exec' ) ) {
            echo $this->errorMassages[4];
            die ( 1 );
        }
    }

    /**
     * Check if PhpWget script exists
     */
    private function checkFileExist() {
        if ( !file_exists( $this->testFilePath ) ) {
            echo $this->errorMassages[3];
            die ( 1 );
        }
    }

    /**
     * Delete the temporary files generated by the test
     */
    private function deleteTempFile($filepath) {
        if ( is_dir( $filepath) ) {
            $this->deleteDir( $filepath );
        } else {
            $deletelFile = unlink( $filepath );
            if ( $deletelFile === false ) {
                echo $this->errorMassages['notice'][1];
            }
        }
    }

    /**
     * Delete the temporary directory generated by the test
     * @param string $dirname
     */
    private function deleteDir($dirname) {
        $dh = opendir( $dirname );
        while ( $file = readdir( $dh ) ) {
            if ( $file != '.' && $file != '..' ) {
                $fullpath = $dirname . '/' . $file;
                if ( !is_dir( $fullpath ) ) {
                    unlink( $fullpath );
                } else {
                    $this->deleteDir( $fullpath );
                }
            }
        }
        closedir( $dh );
        $rd = rmdir( $dirname );
        if ( !$rd ) {
            echo $this->errorMassages['notice'][2];
        }
    }

    /**
     * Test 1
     *
     * Test whether PhpWget can download files from Internet
     */
    public function testDownloadFile() {
        exec( "php $this->testFilePath -uhttp://baidu.com" );
        $filename = 'index.html';
        if ( !file_exists( $filename ) ) {
            echo $this->errorMassages['error'][1];
            die ( 1 );
        }
        $this->deleteTempFile( $this->tempFilePath[1] );
        echo $this->passMassage[1];
    }

    /**
     * Test 2
     *
     * Test whether PhpWget can correctly download files containing multi-level domain URL
     */
    public function testDownloadMultlLevelDomainURL() {
        exec( "php $this->testFilePath -uhttp://www.baidu.com" );
        $filename = 'index.html';
        if ( !file_exists( $filename ) ) {
            echo $this->errorMassages['error'][2];
            die ( 1 );
        }
        $this->deleteTempFile( $this->tempFilePath[2] );
        echo $this->passMassage[2];
    }

    /**
     * Test 3
     *
     * Test whether PhpWget can follow the redirect to download file
     */
    public function testFollowRedirect() {
        exec( "php $this->testFilePath -uhttps://github.com/RazeSoldier/PhpWget/archive/v0.1.tar.gz" );
        $filename = 'v0.1.tar.gz';
        if ( !file_exists( $filename ) ) {
            echo $this->errorMassages['error'][3];
            die ( 1 );
        }
        $this->deleteTempFile( $this->tempFilePath[3] );
        echo $this->passMassage[3];
    }

    public function testEnd() {
        echo $this->passMassage['final'];
    }

    /**
     * Test 4
     *
     * Test whether PhpWget can extract the archive after download
     */
    public function testExtractArchive() {
        exec( "php $this->testFilePath -uhttps://codeload.github.com/RazeSoldier/PhpWget/tar.gz/v0.1 --UZ" );
        $dirname = 'PhpWget-0.1';
        if ( !file_exists( $dirname ) ) {
            echo $this->errorMassages['error'][4];
            die ( 1 );
        }
        $this->deleteTempFile( $this->tempFilePath[4][0] );
        $this->deleteTempFile( $this->tempFilePath[4][1] );
        echo $this->passMassage[4];
    }
}

$test = new \PhpWget\Test();
$test->testDownloadFile(); //Test whether PhpWget can download files from Internet
$test->testDownloadMultlLevelDomainURL(); //Test whether PhpWget can correctly download files containing multi-level domain URL
$test->testFollowRedirect(); //Test whether PhpWget can follow the redirect to download file
$test->testExtractArchive(); //Test whether PhpWget can extract the archive after download
$test->testEnd();