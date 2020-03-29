<?php

namespace Drupal\username_policy\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if a value is a valid user name.
 *
 * @Constraint(
 *   id = "UserNamePattern",
 *   label = @Translation("User name pattern", context = "Validation"),
 * )
 */
class UserNamePatternConstraint extends Constraint {

  public $spaceBeginMessage = 'The username cannot begin with a space.';
  public $spaceEndMessage = 'The username cannot end with a space.';
  public $multipleSpacesMessage = 'The username cannot contain multiple spaces in a row.';
  public $illegalMessage = 'The username contains an illegal character.';
  public $tooLongMessage = 'The username %name is too long: it must be %max characters or less.';

}
