<?php
namespace Rnr\Tests\Ftp;
use Rnr\Ftp\Ftp;

/**
 * @author Sergei Melnikov <me@rnr.name>
 */
class FtpTest extends TestCase
{
    /** @var Ftp */
    private $ftp;

    protected function setUp()
    {
        parent::setUp();

        $this->ftp = new Ftp();

        $this->ftp
            ->setHost('127.0.0.1')
            ->setLogin('root')
            ->setPassword('masterkey')
            ->setSsl(true)
            ->setPassive(true);
    }


    public function testListOfFiles() {
        $files = $this->ftp->flist('/');
    }

    public function testDownloadFile() {
        $file = $this->ftp->get('/Outgoing/Files/AdmireMe-1.jpg');

        $this->assertEquals('4c88962c4e499717aab439b2b6a2b268', md5($file));
    }

    public function testUploadFile() {
        $str = 'Test';
        $response = $this->upload('/Outgoing/Files/test.txt', $str);

        $this->fail($response);
    }

    public function testDeleteFile() {
        $response = $this->ftp->delete('/Outgoing/Files/test.txt');

        $this->fail($response);
    }

    protected function upload($file, $content = 'Test') {
        $str = 'Test';
        $handle = fopen('php://memory', 'r+');

        fputs($handle, $str);

        rewind($handle);

        $response = $this->ftp->put('/Outgoing/Files/test.txt', $handle, strlen($str));

        fclose($handle);

        return $response;
    }
}