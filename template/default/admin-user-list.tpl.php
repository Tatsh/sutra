<?php
/**
 * Available variables:
 * - $users - fRecordSet of User objects.
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package SutraTemplate
 * @link http://www.example.com/
 *
 * @version 1.0
 */
?>
<header><h2><?php print __('Users'); ?></h2></header>
<table>
  <thead>
    <tr>
      <th><?php fCRUD::printSortableColumn('name'); ?></th>
      <th><?php fCRUD::printSortableColumn('date_created', __('Date Registered')); ?></th>
    </tr>
  </thead>

  <tbody>
    <?php foreach ($users as $user): ?>
      <tr class="<?php print fCRUD::getRowClass($user->getUserId(), $user_id); ?>">
        <td><?php print $user->encodeName(); ?></td>
        <td><?php print $user->encodeDateCreated('Y-m-d h:i:s'); ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
