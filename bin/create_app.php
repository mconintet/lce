<?php

include(__DIR__ . '/../cli/argument/Argument.php');
include(__DIR__ . '/../cli/argument/ArgumentGroup.php');
include(__DIR__ . '/../cli/argument/Validation.php');

use \Lce\cli\argument\Argument;
use \Lce\cli\argument\ArgumentGroup;
use \Lce\cli\argument\Validation;

define('DS', DIRECTORY_SEPARATOR);

class AppCreator
{
    private $_argv;

    private $_applicationDir;

    private $_applicationDirParent;

    private $_applicationNamespaceRoot;

    private $_lceWebDir;

    function __construct($argv)
    {
        $this->_argv = $argv;
    }

    public function msg($msg)
    {
        echo $msg . PHP_EOL;
    }

    private function _createApplicationDir()
    {
        echo 'Are you sure to create application directory ' . $this->_applicationDir . ' ? [Y/n]' . PHP_EOL;

        if (fgetc(STDIN) !== 'Y')
            exit;

        echo 'Start to create application directory ' . $this->_applicationDir;
        if (@mkdir($this->_applicationDir, 0755, true)) {
            $this->msg("\tOK");
        } else {
            die("\tFailed.\nPlease check your permission or if that directory already exists." . PHP_EOL);
        }
    }

    private function _createApplicationStructureDirs()
    {
        $this->msg('Start to create application structure directories in this path' . $this->_applicationDir);

        $dirs2Create = array(
            'assets',
            'code',
            'code/components',
            'code/configs',
            'code/controllers',
            'code/data',
            'code/models',
            'code/observers',
            'code/templates',
            'code/views',
            'var',
            'var/session'
        );

        $flag = true;
        foreach ($dirs2Create as $dir) {
            if (!$flag) {
                break;
            }

            $dir = $this->_applicationDir . DS . $dir;
            $msg = 'Creating dir ' . $dir;

            if (@mkdir($dir, 0755, true)) {
                $msg .= "\tOK.";
            } else {
                $msg .= "\tFailed.";
                $flag = false;
            }

            $this->msg($msg);
        }

        $flag or die('Please check your permission or some directories already exist.' . PHP_EOL);
    }

    private function _createMainConfig()
    {
        $template = implode(DS, array(
            __DIR__,
            'create_app_templates',
            'main_config.phtml'
        ));

        echo 'Creating main config';

        ob_start();
        include($template);
        $mainConfig = ob_get_contents();
        ob_end_clean();
        $target2Create = implode(DS, array(
            $this->_applicationDir,
            'code',
            'configs',
            'main.php'
        ));

        $mainConfig = '<?php ' . PHP_EOL . $mainConfig;
        if (file_put_contents($target2Create, $mainConfig)) {
            echo "\tOK.\n";
        } else {
            echo "\tFailed.\n";
            die("Please check your permission.\n");
        }
    }

    private function _createIndexController()
    {
        $template = implode(DS, array(
            __DIR__,
            'create_app_templates',
            'index_controller.phtml'
        ));

        echo 'Creating index controller';

        ob_start();
        include($template);
        $mainConfig = ob_get_contents();
        ob_end_clean();
        $target2Create = implode(DS, array(
            $this->_applicationDir,
            'code',
            'controllers',
            'Index.php'
        ));

        $mainConfig = '<?php ' . PHP_EOL . $mainConfig;
        if (file_put_contents($target2Create, $mainConfig)) {
            echo "\tOK.\n";
        } else {
            echo "\tFailed.\n";
            die("Please check your permission.\n");
        }
    }

    private function _createRewriteHtaccess()
    {
        $template = implode(DS, array(
            __DIR__,
            'create_app_templates',
            're_write_htaccess.txt'
        ));

        echo 'Creating rewrite .htaccess';

        ob_start();
        include($template);
        $mainConfig = ob_get_contents();
        ob_end_clean();
        $target2Create = implode(DS, array(
            $this->_applicationDir,
            '.htaccess-example'
        ));

        $mainConfig = PHP_EOL . $mainConfig;
        if (file_put_contents($target2Create, $mainConfig)) {
            echo "\tOK.\n";
        } else {
            echo "\tFailed.\n";
            die("Please check your permission.\n");
        }
    }

    private function _createBootstrap()
    {
        $template = implode(DS, array(
            __DIR__,
            'create_app_templates',
            'bootstrap.phtml'
        ));

        echo 'Creating bootstrap';

        ob_start();
        include($template);
        $mainConfig = ob_get_contents();
        ob_end_clean();
        $target2Create = implode(DS, array(
            $this->_applicationDir,
            'index.php'
        ));

        $mainConfig = '<?php ' . PHP_EOL . $mainConfig;
        if (file_put_contents($target2Create, $mainConfig)) {
            echo "\tOK.\n";
        } else {
            echo "\tFailed.\n";
            die("Please check your permission.\n");
        }
    }

    public function run()
    {
        umask(0022);
        date_default_timezone_set('Asia/Shanghai');

        $argumentGroup = new ArgumentGroup($this->_argv);

        $typeArgument = new Argument('--type', 'Application type you want to create. Current is only \'webApp\'.');
        $typeArgument->addValidation(
            new Validation(
                function () {
                    return $this->value === 'webApp';
                },
                'Please choose a application type. Current is only \'webApp\'.'
            ));
        $argumentGroup->add($typeArgument);

        $pathArgument = new Argument('--path', 'The path your application be located.');
        $pathArgument->addValidation(
            new Validation(
                function () {
                    $this->value = empty($this->value) ? getcwd() : $this->value;
                    $this->value = realpath($this->value);
                    return true;
                },
                ''
            )
        );
        $argumentGroup->add($pathArgument);

        $namespaceArgument = new Argument('--namespace',
            'Namespace of your application, first character will be capitalized automatically.');
        $namespaceArgument->addValidation(
            new Validation(
                function () {
                    if (preg_match('/^([a-zA-Z]([a-zA-Z0-9_]\\\\)*)+$/', $this->value) === 1) {
                        $this->value = ucfirst($this->value);
                        return true;
                    }

                    return false;
                },
                'Please provide a valid namespace of your application.'
            )
        );
        $argumentGroup->add($namespaceArgument);

        $helpArgument = new Argument('--help', 'Print this help.', true);
        $helpArgument->addValidation(
            new Validation(
                function () {
                    if ($this->value !== null) {
                        echo $this->argumentsGroup->usage();
                        exit;
                    } else {
                        return true;
                    }
                },
                ''
            )
        );
        $argumentGroup->add($helpArgument);

        $isValid = $argumentGroup->isValid();
        if ($isValid === true) {
            $this->_lceWebDir = realpath(
                implode(DS,
                    array(
                        __DIR__,
                        '..',
                        'web',
                        'Application.php'
                    )
                )
            );

            $this->_applicationDirParent = $pathArgument->value;
            $this->_applicationNamespaceRoot = $namespaceArgument->value;
            $this->_applicationDir = rtrim($pathArgument->value, DS) . DS .
                str_replace('\\', DS, $this->_applicationNamespaceRoot);

            $this->_createApplicationDir();
            $this->_createApplicationStructureDirs();
            $this->_createMainConfig();
            $this->_createIndexController();
            $this->_createRewriteHtaccess();
            $this->_createBootstrap();

            $this->msg(PHP_EOL . 'Wow!');

            $path = str_replace('\\', '/', $this->_applicationNamespaceRoot);
            $this->msg(sprintf('Now you can visit your application via http://127.0.0.1/%s', $path));
            $this->msg('or');
            $this->msg(sprintf('http://127.0.0.1/%s?r=index/index', $path));
        } else {
            echo $isValid . PHP_EOL;
        }
    }
}

$appCreator = new AppCreator($argv);
$appCreator->run();