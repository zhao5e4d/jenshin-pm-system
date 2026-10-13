<?php
declare(strict_types=1);
/**
 * php test/jenshin/collab-workbench.php
 * 只在本地数据库连接内创建临时表，所有测试写入随连接关闭消失。
 */
if(PHP_SAPI !== 'cli') exit('CLI only');
$root = dirname(__DIR__, 2);
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/www/index.php';
$_SERVER['REQUEST_URI'] = '/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = '127.0.0.1:8080';
$_SERVER['HTTP_USER_AGENT'] = 'Jenshin regression';
require_once $root . '/framework/router.class.php';
require_once $root . '/framework/control.class.php';
require_once $root . '/framework/model.class.php';
require_once $root . '/framework/helper.class.php';
$app = router::createApp('pms', $root, 'router');
if(!in_array($config->db->host, array('127.0.0.1', 'localhost', '::1'), true)) exit("Local database required\n");
require_once $root . '/module/common/model.php';
require_once $root . '/module/common/tao.php';
$app->user = (object)array('account' => 'jx-read-test-a', 'admin' => true);
$app->loadLang('jxcollab');
require_once $root . '/module/jxcollab/model.php';
require_once $root . '/module/jxcore/model.php';

class workbenchCollabTestModel extends jxcollabModel
{
    public function ensureSchema(): bool { return true; }
}

function check(bool $condition, string $message): void
{
    if(!$condition) throw new RuntimeException($message);
    echo "PASS $message\n";
}

try
{
    foreach(array_unique(array(TABLE_JX_COLLAB, TABLE_JX_COLLABMEMBER, TABLE_TASK, TABLE_PROJECT, TABLE_TODO)) as $table)
    {
        $table = trim($table, '`');
        $definition = $dbh->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $dbh->exec(preg_replace('/^CREATE TABLE/', 'CREATE TEMPORARY TABLE', $definition));
    }
    $memberTable = trim(TABLE_JX_COLLABMEMBER, '`');
    if($dbh->query("SHOW COLUMNS FROM `$memberTable` LIKE 'readDate'")->fetch()) $dbh->exec("ALTER TABLE `$memberTable` DROP COLUMN `readDate`");
    $dbh->exec("INSERT INTO `$memberTable` (collab, account, content, settled) VALUES (1, 'jx-read-test-a', 'existing feedback', 0)");
    $core = new jxcoreModel();
    $migration = new ReflectionMethod(jxcoreModel::class, 'ensureCollabReadSchema');
    check($migration->invoke($core), 'old schema adds readDate');
    check($migration->invoke($core), 'repeated migration succeeds');
    $existing = $dbh->query("SELECT content, readDate FROM `$memberTable` WHERE collab=1")->fetch();
    check($existing->content === 'existing feedback' && empty($existing->readDate), 'migration preserves feedback and defaults existing items to unread');

    $collabTable = trim(TABLE_JX_COLLAB, '`');
    $stmt = $dbh->prepare("INSERT INTO `$collabTable` (id, title, type, status, deleted) VALUES (?, ?, 'consult', 'doing', 0)");
    $members = $dbh->prepare("INSERT INTO `$memberTable` (collab, account, settled) VALUES (?, ?, 0)");
    for($id = 1; $id <= 55; $id++)
    {
        $stmt->execute(array($id, 'Test item ' . $id));
        if($id !== 1) $members->execute(array($id, 'jx-read-test-a'));
    }
    $members->execute(array(1, 'jx-read-test-b'));
    $collab = new workbenchCollabTestModel();
    check(count($collab->getMyPendingList('', 50)) === 50 && $collab->getMyPendingCount() === 55, 'total count is independent of 50-row limit');
    check($collab->getMyPendingList()[1]->isUnread === true, 'existing item is unread');
    check($collab->markRead(1), 'current collaborator can mark item read');
    $firstRead = $dbh->query("SELECT readDate FROM `$memberTable` WHERE collab=1 AND account='jx-read-test-a'")->fetchColumn();
    check(!empty($firstRead) && $collab->getMyPendingList()[1]->isUnread === false, 'read status is persisted');
    check(!empty($collab->getMembers(1)[1]->readDate), 'detail member data includes read timestamp');
    check($collab->markRead(1) && $firstRead === $dbh->query("SELECT readDate FROM `$memberTable` WHERE collab=1 AND account='jx-read-test-a'")->fetchColumn(), 'repeated read preserves timestamp');
    check($collab->getMyPendingCount() === 55, 'reading does not reduce pending count');
    $app->user->account = 'jx-read-test-b';
    check($collab->getMyPendingList()[1]->isUnread === true, 'another collaborator stays unread');
    check(!$collab->markRead(2), 'non-member cannot mark item read');
    check(!$collab->markRead(99999), 'unknown item cannot be marked read');
    check($collab->getMyPendingCount('no-such-account') === 0, 'empty pending count is zero');
    $app->user->account = 'jx-read-test-a';
    $dbh->exec("UPDATE `$collabTable` SET title='Edited item' WHERE id=1");
    check(!$collab->getMyPendingList()[1]->isUnread, 'editing does not reset read status');
    $dbh->exec("UPDATE `$memberTable` SET settled=1 WHERE collab=1 AND account='jx-read-test-a'");
    check($collab->getMyPendingCount() === 54, 'final feedback removes item from pending count');
    $dbh->exec("UPDATE `$collabTable` SET status='canceled' WHERE id=2");
    $dbh->exec("UPDATE `$collabTable` SET status='closed' WHERE id=3");
    $dbh->exec("UPDATE `$collabTable` SET deleted=1 WHERE id=4");
    check($collab->getMyPendingCount() === 51, 'canceled closed and deleted items excluded');
    $members->execute(array(55, 'jx-read-test-c'));
    $app->user->account = 'jx-read-test-c';
    check($collab->getMyPendingCount() === 1 && $collab->getMyPendingList()[55]->isUnread, 'new collaborator gets one unread item');

    $app->user->account = 'jx-read-test-a';
    $block = $core->loadModel('block');
    /* MySQL 临时表不能自连接；挂起任务的既有查询不在本次修改范围，提供其返回值。 */
    class workbenchBlockTestModel extends extblockModel
    {
        public function loadModel($moduleName, $appName = ''): object|bool
        {
            if($moduleName !== 'task') return parent::loadModel($moduleName, $appName);
            return new class
            {
                public function getUserSuspendedTasks(string $account): array { return array(101 => (object)array('id' => 101)); }
            };
        }
    }
    $block = new workbenchBlockTestModel();
    $todoTable = trim(TABLE_TODO, '`');
    $todo = $dbh->prepare("INSERT INTO `$todoTable` (assignedTo, type, objectID, status, cycle, vision, deleted) VALUES ('jx-read-test-a', ?, ?, ?, ?, ?, ?)");
    for($i = 0; $i < 55; $i++) $todo->execute(array('custom', 0, 'wait', 0, $config->vision, 0));
    $todo->execute(array('task', 101, 'wait', 0, $config->vision, 0));
    $todo->execute(array('custom', 0, 'done', 0, $config->vision, 0));
    $todo->execute(array('custom', 0, 'wait', 1, $config->vision, 0));
    $todo->execute(array('custom', 0, 'wait', 0, 'other', 0));
    $todo->execute(array('custom', 0, 'wait', 0, $config->vision, 1));
    check($block->getJxPendingTodoCount() === 55, 'todo total excludes suspended tasks done cyclic other-vision and deleted items');
    $app->user->account = 'no-such-account';
    check($block->getJxPendingTodoCount() === 0, 'zero todo count');
    $app->user->account = 'jx-read-test-a';
    $dbh->exec("ALTER TABLE `$memberTable` DROP COLUMN `readDate`");
    check(!$collab->markRead(55), 'database write failure does not report success');
    $install = (string)file_get_contents($root . '/db/jenshin/install.sql');
    if(!preg_match('/CREATE TABLE IF NOT EXISTS `zt_jx_collabmember`[\s\S]+?;/', $install, $match)) throw new RuntimeException('Missing member install schema');
    $dbh->exec("DROP TEMPORARY TABLE `$memberTable`");
    $ddl = str_replace(array('CREATE TABLE IF NOT EXISTS', '`zt_jx_collabmember`'), array('CREATE TEMPORARY TABLE', "`$memberTable`"), $match[0]);
    $dbh->exec($ddl);
    check((bool)$dbh->query("SHOW COLUMNS FROM `$memberTable` LIKE 'readDate'")->fetch(), 'fresh install includes readDate');
    $dbh->exec("INSERT INTO `$memberTable` (collab, account) VALUES (99, 'jx-read-test-a')");
    check(empty($dbh->query("SELECT readDate FROM `$memberTable` WHERE collab=99")->fetch()->readDate), 'fresh install defaults new members to unread');
    echo "All workbench regression checks passed. Temporary fixtures only.\n";
}
catch(Throwable $error)
{
    fwrite(STDERR, 'FAIL ' . $error->getMessage() . "\n");
    exit(1);
}
