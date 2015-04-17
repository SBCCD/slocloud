<?php /*

SLO Cloud - A Cloud-Based SLO Reporting Tool for Higher Education

This is a peer-reviewed, open-source, public project made possible by the Open Innovation in Higher Education project.

Copyright (C) 2015 Jesse Lawson, San Bernardino Community College District

Contributors:
Jesse Lawson
Jason Brady

THIS PROJECT IS LICENSED UNDER GPLv2. YOU MAY COPY, DISTRIBUTE AND MODIFY THE SOFTWARE AS LONG AS YOU TRACK
CHANGES/DATES OF IN SOURCE FILES AND KEEP ALL MODIFICATIONS UNDER GPL. YOU CAN DISTRIBUTE YOUR APPLICATION USING A
GPL LIBRARY COMMERCIALLY, BUT YOU MUST ALSO DISCLOSE THE SOURCE CODE.

GNU General Public License Version 2 Disclaimer:

---

This file is part of SLO Cloud

SLO Cloud is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later.

SLO Cloud is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free
Software Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA or
visit http://opensource.org/licenses/GPL-2.0

---

*/
namespace SLOCloud;

use Doctrine\Common\EventManager;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\Setup;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use SLOCloud\Db\DoctrineExtensions\TablePrefix;
use SLOCloud\Db\FileSQLLogger;
use SLOCloud\Model\ErrorResult;
use SLOCloud\Model\Service\SLOService;
use SLOCloud\Model\Storage\SLO;
use Slim\Slim;
use Slim\Views;
use SLOCloud\Model\Storage\Mapping\CustomSQLServer2008Platform;
use Twig_Extension_Debug;

/**
 * These annotations are to remove warnings in PhpStorm. Helps with code completion as well.
 * @package SLOCloud
 * @property EntityManager $db
 * @property EventManager $dbEvm
 * @property SLOService $sloService
 * @property mixed $config
 * @property mixed $view
 * @property Logger $log
 * @property mixed $data
 */
class Application extends Slim
{
    /** @var string */
    public $accountId = '';
    /** @var string */
    public $username = '';
    /** @var string */
    public $email = '';
    /** @var string */
    public $type = '';

    public function __construct($config, $additionalConfig)
    {
        parent::__construct($config);
        $this->accountId = $this->session('accountId');
        $this->username = $this->session('username');
        $this->email = $this->session('email');
        $this->type = $this->session('type');

        $app = $this;

        $app->add(new Middleware\AutomaticTransactions());
        $app->config($additionalConfig);

        //Todo: make log file configurable
        $app->container->singleton('log', function () use ($app) {
            if ($app->isDebug()) {
                $level = Logger::DEBUG;
            } else {
                $level = Logger::INFO;
            }
            $log = new Logger($app->getName());
            $handler = new RotatingFileHandler('../var/logs/app.log', 0, $level, true, null, true);
            $handler->setFormatter(new Formatter\ExceptionLineFormatter);
            $processor = new WebProcessor();
            $processor->addExtraField('user_agent', 'HTTP_USER_AGENT');
            $log->pushHandler($handler);
            $log->pushProcessor($processor);
            return $log;
        });

        $app->container->singleton('dbEvm', function () use ($app) {
            return new EventManager();
        });

        $app->container->singleton('db', function () use ($app) {
            return $app->createEntityManager($app->config('db'), $app->log, $app->dbEvm);
        });

        $app->container->singleton("data", function () use ($app) {
            return loadData($app, $app->container->get("settings"));
        });

        $app->container->singleton("sloService", function () use ($app) {
            $type = $app->config('model')['slo_type'];
            $class = "SLOCloud\\Model\\Service\\".$type."SLOService";
            return new $class($app->db, $app->data);
        });

        $app->view(new Views\Twig());
        $app->view->parserOptions = [
            'charset' => 'utf-8',
            'cache' => realpath('../var/cache'),
            'auto_reload' => true,
            'strict_variables' => true,
            'autoescape' => true,
            'debug' => $app->isDebug()
        ];
        $app->view->parserExtensions = [
            new Views\TwigExtension(),
            new TwigExtension($this->data, $this->isDebug()),
            new Twig_Extension_Debug()
        ];

        // Log all requests to the app
        $app->hook('slim.before.dispatch', function () use ($app) {
            $app->writeInfo($app->getRoutePath());
        });
        $app->hook('slim.before', function () use ($app) {
            // Introduce a little lag time, to test slow loading
            if ($app->isDebug() && $app->config('slow-load-test')) {
                usleep(250000);
            }

            if ($app->config('https') && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
                $app->redirectToHTTPS();
            }
        });

        $app->error([$this, "errorHandler"]);
        $app->notFound([$this, "notFoundHandler"]);
    }

    public function saveLogin($accountId, $type)
    {
        $ldap = $this->ldap();

        $username = $ldap->getUser($type, $accountId);
        if ($username === false) {
            $this->log->error("Failed get username. (".$ldap->lastError().")");
            $username = "failed to get username";
        }

        $this->session('accountId', $accountId);
        $this->session('type', $type);
        $this->session('username', $username);

        $email = $ldap->getUserAttribute($type, $accountId, 'mail');
        if ($email === false) {
            $error = "Failed to pull email address. Email functions are disabled. (".$ldap->lastError().")";
            $this->log->error($error);
            $this->flash('error', $error);
        }

        $this->session('email', $email);
    }

    // We override this function because Slim doesn't redirect to the relative root
    // on an empty $url. We calculate one in that case.
    // We also want to log redirects!
    public function redirect($url, $status = null)
    {
        if ($url === '') {
            $url = $this->request->getScriptName();
        }
        if ($url === '') {
            $url = "/";
        }

        if ($status === null) {
            $this->log->info("Redirect: $url");
            parent::redirect($url);
        } else {
            $this->log->info("Redirect($status): $url");
            parent::redirect($url, $status);
        }
    }

    // Set default data before passing on to Slim
    public function render($template, $data = array(), $status = null)
    {
        $app = $this;
        $data['page'] = $app->getRoutePath();
        $data['isDebug'] = $app->isDebug();
        $data['userIsAdmin'] = $app->userIsAdmin();
        $data['isLoginEnabled'] = !$app->config('disable.login');
        $data['allowReset'] = $app->config('allow-resets');
        $data['model'] = $app->sloService->getType();
        $data['shortName'] = $app->config('institution')['shortName'];
        $data['periods'] = [
            'last3' => 'Last 3 Years',
            'annual' => 'Annual',
            'sp' => 'Spring',
            'fa' => 'Fall',
            'sm' => 'Summer'
        ];
        parent::render($template, $data, $status);
    }

    public function session($name, $value = null)
    {
        global $_SESSION;
        if ($_SESSION === null) {
            $this->writeError("Sessions not enabled");
            return "";
        }
        if ($value === null) {
            if (array_key_exists($name, $_SESSION)) {
                return $_SESSION[$name];
            } else {
                return '';
            }
        } else {
            $_SESSION[$name] = $value;
            $names = get_object_vars($this);

            if (array_key_exists($name, $names)) {
                $this->$name = $value;
            }
            return $_SESSION[$name];
        }
    }

    public function sendSLOSubmitEmail(SLO $SLO)
    {
        if ($this->email !== "") {
            $this->view()->appendData(['SLO' => $SLO]);
            $type = $this->sloService->getType();
            $body = $this->view()->fetch("email/$type/SLOSubmit.html.twig");

            $subject = 'SLO submission for: '.$this->config('institution')['shortName'].', '
                .$SLO->getTerm().', '.$SLO->getSection();
            $transport = \Swift_SmtpTransport::newInstance(
                $this->config('smtp')['server'],
                $this->config('smtp')['port']
            );
            $transport->setTimeout($this->config('smtp')['timeout']);
            $mailer = \Swift_Mailer::newInstance($transport);

            $message = \Swift_Message::newInstance($subject, $body, 'text/html')
                ->setFrom([$this->config('smtp')['from']])
                ->setTo($this->email);

            $mailer->send($message);
        }
    }

    public function requireAuthentication()
    {
        /** @var \SLOCloud\Application $app */
        $app = Slim::getInstance();
        $disableLogin = $app->config('disable.login');
        if (!$disableLogin) {
            if (!$this->isAuthenticated()) {
                $app->flash('error', 'Login required');
                $app->redirect('login?page='.$_SERVER['REQUEST_URI']);
            }
        }
    }

    public function requireAuthenticationAJAX()
    {
        /** @var \SLOCloud\Application $app */
        $app = Slim::getInstance();
        $disableLogin = $app->config('disable.login');
        if (!$disableLogin) {
            if (!$this->isAuthenticated()) {
                $app->status(403);
                $app->response->headers->set('Content-Type', 'application/json');
                $app->response->headers->set('Cache-Control', 'no-cache');
                $app->flashKeep();
                $app->writeError("Attempt to access AJAX url without login");
                echo new ErrorResult("You are not logged in. You must be logged in to use this resource.");
                $app->stop();
            }
        }
    }

    public function redirectToHTTPS()
    {
        $this->redirect(str_replace("http://", "https://", $this->fullUrl()));
    }

    public function isAuthenticated()
    {
        return $this->accountId !== '';
    }

    public function writeInfo($msg, $data = null)
    {
        $context = [$this->config('institution')['shortName'], $this->accountId, $this->username];
        if ($data !== null) {
            $context = array_merge($context, $data);
        }
        $this->log->info($msg, $context);
    }

    public function writeDebug($msg, $data = null)
    {
        $context = [$this->config('institution')['shortName'], $this->accountId, $this->username];
        if ($data !== null) {
            $context = array_merge($context, $data);
        }
        $this->log->debug($msg, $context);
    }

    public function writeError($msg, $data = null)
    {
        $context = [$this->config('institution')['shortName'], $this->accountId, $this->username];
        if ($data !== null) {
            $context = array_merge($context, $data);
        }
        $this->log->error($msg, $context);
    }

    /**
     * Return provided data encoded as JSON
     * @param mixed $data
     */
    public function returnJson($data)
    {
        $app = $this;
        $json = json_encode($data);

        if ($json === false) {
            $app->error("JSON Encoding failure: " . json_last_error_msg(), json_last_error());
        }

        $app->flashKeep();
        $app->etag(md5($json));
        $app->response->headers->set('Content-Type', 'application/json');
        $app->response->headers->set('Cache-Control', 'no-cache');
        echo $json;
    }

    public function data($key)
    {
        $data = $this->container->get('data');
        return isset($data[$key]) ? $data[$key] : null;
    }

    public function ldap()
    {
        return new Ldap($this->config('ldap'), [&$this, 'writeDebug'], [&$this, 'writeError']);
    }

    public function queryString()
    {
        return ($this->environment['QUERY_STRING'] === '' ? '' : '?'.$this->environment['QUERY_STRING']);
    }

    public function fullUrl()
    {
        $queryString = $this->environment['QUERY_STRING'];
        $queryString = ($queryString === ''?'':'?'.$queryString);
        return $this->request->getUrl().$this->request->getScriptName().$queryString;
    }

    public function notFoundHandler()
    {
        $app = $this;
        $app->writeError("Page Not Found");
        $error = "Page Not Found";
        if ($app->request->headers('X-Requested-With') === 'XMLHttpRequest') {
            echo json_encode(new ErrorResult($error));
        } else {
            $app->defaultNotFound();
        }
    }

    public function errorHandler(\Exception $e)
    {
        $app = $this;
        $app->writeError("Error", ['exception' => $e]);
        $error = "Request failed for an unknown reason";
        if ($app->request->headers('X-Requested-With') === 'XMLHttpRequest') {
            echo json_encode(new ErrorResult($error));
        } else {
            $this->defaultError($e);
        }
    }

    public function cacheAndServe($uniqueId, $lastModified, $contentType, $GenerateContent)
    {
        $app = $this;
        $cacheDir = realpath('../var/cache');
        $name = "$cacheDir/".hash('md5', $uniqueId).self::fileExt($contentType);
        if (!file_exists($name) || filemtime($name) !== $lastModified) {
            if (!file_exists($name)) {
                $app->writeInfo("Cache file missing for '$uniqueId', generating...");
            } else {
                $app->writeInfo(
                    "Last Modified mismatch. Was ".date('c', filemtime($name)).
                    ", needed to be ".date('c', $lastModified).", generating..."
                );
            }
            $contents = call_user_func($GenerateContent);
            file_put_contents($name, $contents);
            touch($name, $lastModified);
        } else {
            $contents = file_get_contents($name);
        }

        $cacheTime = $app->config('resource.cachetime');
        $app->etag(md5($contents));
        $app->lastModified($lastModified);
        $app->expires("+".$cacheTime." minutes");
        $app->response->headers->set('Cache-Control', 'max-age='.($cacheTime*60));
        $app->response->headers->set('Content-Type', $contentType);
        echo $contents;
    }

    public function hasUploadErrored($file)
    {
        return $file['error'] !== UPLOAD_ERR_OK;
    }

    public function uploadError($file)
    {
        $message = 'Error uploading file';
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                $message .= ' - no error';
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $message .= ' - file too large (limit of '.ini_get('upload_max_filesize').' bytes).';
                break;
            case UPLOAD_ERR_PARTIAL:
                $message .= ' - file upload was not completed.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $message .= ' - zero-length file uploaded.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message .= ' - missing temporary folder.';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message .= ' - failed to write to disk.';
                break;
            case UPLOAD_ERR_EXTENSION:
                $message .= ' - upload stopped by extension.';
                break;
            default:
                $message .= ' - internal error #'.$file['error'];
                break;
        }

        return $message;
    }

    /**
     * @return bool
     */
    public function userIsAdmin()
    {
        return in_array($this->accountId, explode(',', $this->config('admin-ids')));
    }

    /**
     * @param string $accountId
     * @return boolean
     */
    public function isAllowedUser($accountId) {
        return !$this->config('restrict-user-ids') || in_array($accountId, array_merge(explode(',', $this->config('admin-ids')), explode(',', $this->config('allowed-user-ids'))));
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return !!$this->config('debug');
    }

    private static function fileExt($contentType)
    {
        $map = array(
            'application/pdf'   => '.pdf',
            'application/zip'   => '.zip',
            'image/gif'         => '.gif',
            'image/jpeg'        => '.jpg',
            'image/png'         => '.png',
            'text/css'          => '.css',
            'text/html'         => '.html',
            'text/javascript'   => '.js',
            'text/plain'        => '.txt',
            'text/xml'          => '.xml',
        );
        if (isset($map[$contentType])) {
            return $map[$contentType];
        }

        return ".tmp";
    }

    private function getRoutePath()
    {
        $app = $this;
        $route = $app->router->getCurrentRoute();
        $params = $route->getParams();
        $pattern = $route->getPattern();
        foreach ($params as $name => $value) {
            $pattern = str_replace(":" . $name, urlencode($value), $pattern);
        }
        return $pattern;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getReportingTerms()
    {
        $app = $this;
        $terms = array_merge(
            $app->sloService->getTermsOnRecord(),
            $app->data('termsList')
        );
        sortTerms($terms);
        return $terms;
    }

    public static function createEntityManager($conn, $logger = null, $eventManager = null)
    {
        $config = Setup::createYAMLMetadataConfiguration([$conn["mappings"]], true);
        $config->setProxyDir($conn["proxy-dir"]);
        $config->setAutoGenerateProxyClasses(AbstractProxyFactory::AUTOGENERATE_NEVER);

        if ($eventManager === null) {
            $eventManager = new EventManager();
        }

        $tablePrefix = new TablePrefix($conn['prefix']);
        $eventManager->addEventListener(Events::loadClassMetadata, $tablePrefix);

        if ($conn['driver'] === 'pdo_sqlsrv') {
            $conn['platform'] = new CustomSQLServer2008Platform();
        }

        $entityManager = EntityManager::create($conn, $config, $eventManager);
        $connection = $entityManager->getConnection();
        if ($logger !== null) {
            $connection->getConfiguration()->setSQLLogger(new FileSQLLogger($logger));
        }
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('datetime', 'utc_datetime');
        self::enableSqliteForeignKeys($connection);
        return $entityManager;
    }

    /**
     * @param Connection $conn
     * @throws \Doctrine\DBAL\DBALException
     * @throws \ErrorException
     */
    private static function enableSqliteForeignKeys(Connection $conn)
    {
        if ($conn->getParams()['driver'] === 'pdo_sqlite') {
            $result = $conn->executeQuery('PRAGMA foreign_keys')->fetchAll()[0]['foreign_keys'];
            if ($result === '0') {
                $conn->executeQuery('PRAGMA foreign_keys = ON')->fetchAll();
                if ($conn->executeQuery('PRAGMA foreign_keys')->fetchAll()[0]['foreign_keys'] === $result) {
                    throw new \ErrorException("Failed to enable foreign key support");
                }
            }
        }
    }
}
