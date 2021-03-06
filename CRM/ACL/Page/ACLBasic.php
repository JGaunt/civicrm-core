<?php
/*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 */
class CRM_ACL_Page_ACLBasic extends CRM_Core_Page_Basic {

  /**
   * The action links that we need to display for the browse screen.
   *
   * @var array
   */
  public static $_links = NULL;

  /**
   * Get BAO Name.
   *
   * @return string
   *   Classname of BAO.
   */
  public function getBAOName() {
    return 'CRM_ACL_BAO_ACL';
  }

  /**
   * Get action Links.
   *
   * @return array
   *   (reference) of action links
   */
  public function &links() {
    if (!(self::$_links)) {
      self::$_links = [
        CRM_Core_Action::UPDATE => [
          'name' => ts('Edit'),
          'url' => 'civicrm/acl/basic',
          'qs' => 'reset=1&action=update&id=%%id%%',
          'title' => ts('Edit ACL'),
        ],
        CRM_Core_Action::DELETE => [
          'name' => ts('Delete'),
          'url' => 'civicrm/acl/basic',
          'qs' => 'reset=1&action=delete&id=%%id%%',
          'title' => ts('Delete ACL'),
        ],
      ];
    }
    return self::$_links;
  }

  /**
   * Run the page.
   *
   * This method is called after the page is created. It checks for the
   * type of action and executes that action.
   * Finally it calls the parent's run method.
   */
  public function run() {
    $id = $this->getIdAndAction();

    // set breadcrumb to append to admin/access
    $breadCrumb = [
      [
        'title' => ts('Access Control'),
        'url' => CRM_Utils_System::url('civicrm/admin/access', 'reset=1'),
      ],
    ];
    CRM_Utils_System::appendBreadCrumb($breadCrumb);

    // what action to take ?
    if ($this->_action & (CRM_Core_Action::UPDATE | CRM_Core_Action::ADD | CRM_Core_Action::DELETE)) {
      $this->edit($this->_action, $id);
    }

    // finally browse the acl's
    $this->browse();

    // This replaces parent run, but do parent's parent run
    return CRM_Core_Page::run();
  }

  /**
   * Browse all acls.
   */
  public function browse() {

    // get all acl's sorted by weight
    $acl = [];
    $query = "
  SELECT *
    FROM civicrm_acl
   WHERE ( object_table NOT IN ( 'civicrm_saved_search', 'civicrm_uf_group', 'civicrm_custom_group' ) )
ORDER BY entity_id
";
    $dao = CRM_Core_DAO::executeQuery($query);

    $roles = CRM_Core_OptionGroup::values('acl_role');

    $permissions = CRM_Core_Permission::basicPermissions();
    while ($dao->fetch()) {
      if (!array_key_exists($dao->entity_id, $acl)) {
        $acl[$dao->entity_id] = [];
        $acl[$dao->entity_id]['name'] = $dao->name;
        $acl[$dao->entity_id]['entity_id'] = $dao->entity_id;
        $acl[$dao->entity_id]['entity_table'] = $dao->entity_table;
        $acl[$dao->entity_id]['object_table'] = CRM_Utils_Array::value($dao->object_table, $permissions);
        $acl[$dao->entity_id]['is_active'] = 1;

        if ($acl[$dao->entity_id]['entity_id']) {
          $acl[$dao->entity_id]['entity'] = $roles[$acl[$dao->entity_id]['entity_id']];
        }
        else {
          $acl[$dao->entity_id]['entity'] = ts('Any Role');
        }

        // form all action links
        $action = array_sum(array_keys($this->links()));

        $acl[$dao->entity_id]['action'] = CRM_Core_Action::formLink(
          self::links(),
          $action,
          ['id' => $dao->entity_id],
          ts('more'),
          FALSE,
          'aclRole.manage.action',
          'ACLRole',
          $dao->entity_id
        );
      }
      elseif (!empty($permissions[$dao->object_table])) {
        $acl[$dao->entity_id]['object_table'] .= ", {$permissions[$dao->object_table]}";
      }
    }
    $this->assign('rows', $acl);
  }

  /**
   * Get name of edit form.
   *
   * @return string
   *   Classname of edit form.
   */
  public function editForm() {
    return 'CRM_ACL_Form_ACLBasic';
  }

  /**
   * Get edit form name.
   *
   * @return string
   *   name of this page.
   */
  public function editName() {
    return 'Core ACLs';
  }

  /**
   * Get user context.
   *
   * @param null $mode
   *
   * @return string
   *   user context.
   */
  public function userContext($mode = NULL) {
    return 'civicrm/acl/basic';
  }

}
