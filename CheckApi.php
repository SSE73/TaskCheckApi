<?php
namespace XCTools\Tasks;

use XCTools\Utils\Config;
use XCTools\Utils\Utility;

trait CheckApi{

    public function taskCheckApi($base_dir,$version_commit){
        return new CheckApiTask($base_dir,$version_commit);
    }
}

class CheckApiTask implements \Robo\Contract\TaskInterface
{
    use \Robo\Common\TaskIO;
    use \Robo\Task\Base\loadTasks;
    use \Robo\Task\File\loadTasks;
    use \Robo\Task\FileSystem\loadTasks;

    protected $base_dir_path;
    protected $version_commit;
    protected $checker_dir_path;
    protected $checker_bin_path;

    public function __construct($base_dir,$commit)
    {
        $this->base_dir_path = $base_dir;
        $this->src_dir_path = $this->base_dir_path . "src";
        $this->build_dir_path = $this->base_dir_path . ".dev/build";

        $this->checker_dir_path = $this->build_dir_path . "/vendor/tomzx/php-semver-checker/";

        $this->checker_tests_dir_path = $this->checker_dir_path . "tests/";

        $this->checker_before_dir_path = $this->checker_tests_dir_path . "before/";
        $this->checker_after_dir_path = $this->checker_tests_dir_path . "after/";

        $this->checker_bin_path = $this->checker_dir_path . "/bin/php-semver-checker";

        $this->version_commit = $commit;

    }

    public function run()
    {
        $this->gitArhivCommitToBefore();
        $this->runChecker();
    }

    protected function gitArhivCommitToBefore()
    {

        $path_checker_before = 'git archive --format=tar ' . $this->version_commit . ' classes Includes' . ' | (cd ' . $this->checker_before_dir_path . ' && ' . 'tar xf -)';
        $path_checker_after = 'cp -a classes Includes ' . $this->checker_after_dir_path;

        chdir($this->src_dir_path);
        $this->taskExec($path_checker_before)
            ->run();

        chdir($this->src_dir_path);
        $this->taskExec($path_checker_after)
            ->run();

    }

    protected function runChecker()
    {
        $this->taskExec( $this->checker_bin_path . ' compare ' . $this->checker_before_dir_path . ' ' . $this->checker_after_dir_path )
            ->run();
    }

}
