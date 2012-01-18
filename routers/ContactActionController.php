<?php
/**
 * Manages page requests under /contact.
 *
 * @copyright Copyright (c) 2011 Poluza.
 * @author Andrew Udvare [au] <andrew@poluza.com>
 * @license http://www.opensource.org/licenses/mit-license.php
 *
 * @package SutraRouters
 * @link http://www.example.com/
 *
 * @version 1.0
 */
class ContactActionController extends MoorActionController {
  /**
   * Handles request for the form.
   *
   * @return void
   */
  public function index() {
    if (fAuthorization::checkLoggedIn()) {
      $user = fAuthorization::getUserToken();
      fRequest::set('name', $user->getName());
      fRequest::set('email_address', $user->getEmailAddress());
    }

    $content = sTemplate::buffer('contact-form', array(
      'csrf' => fRequest::generateCSRFToken('/contact/post'),
      'categories' => array(
        __('Feedback'),
        __('General'),
        __('Make a Suggestion'),
        __('Technical Issue'),
        __('Other'),
      ),
      'textfields' => array(
        'name' => array(
          'label' => __('Name:'),
          'required' => TRUE,
        ),
        'email_address' => array(
          'label' => __('E-Mail Address:'),
          'required' => TRUE,
        ),
        'phone_number' => array(
          'label' => __('Phone Number:'),
        ),
      ),
    ));

    sTemplate::render(array(
      'content' => $content,
      'title' => __('Contact'),
    ));
  }

  /**
   * Handles POST request for /contact.
   *
   * @return void
   */
  public function post() {
    $message = fRequest::get('message', 'string', '');
    $message_with_fixed_spaces = preg_replace('/\s+/', ' ', $message);
    $message = str_replace("\n", '', $message_with_fixed_spaces);

    if (!strlen($message) || strlen($message) > 1000) {
      throw new fValidationException(__('Message must not exceed 1000 characters.'));
    }

    $email_address = fRequest::get('email_address', 'string', '');
    if (!preg_match(fEmail::EMAIL_REGEX, $email_address)) {
      throw new fValidationException(__('Invalid e-mail address.'));
    }


    fRequest::set('message', $message_with_fixed_spaces);

    // Log the contact request
    $contact = new ContactMailMessage;
    $contact->populate();
    $contact->store();

    // Email back this person
    $email = new fEmail;
    $email->setFromEmail('admin@poluza.com');
    $email->setSubject(__('Thank you for contacting us'));
    $email->addRecipient($email_address);
    $email->setBody("
    Thank you for your message. If necessary, someone will respond to you as soon as possible.
");
    $email->send();
  }
}
