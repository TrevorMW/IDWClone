<?php
/**
 *
 * Controller for Wholesalers
 *
 */

require_once 'Mage/Customer/controllers/AccountController.php';
require_once ( Mage::getBaseDir('lib') . '/Mailchimp/mailchimp.php');

class Wholesale_Customer_AccountController extends Mage_Customer_AccountController
{

	public function indexAction()
	{
		parent::indexAction();
	}

  public function createwholesaleAction()
  {
    if ($this->_getSession()->isLoggedIn())
    {
      $this->_redirect('*/*');
      return;
    }

    var_dump( Mage::registry('current_customer') );
    die();

    $this->loadLayout();
    $this->_initLayoutMessages('customer/session');
    $this->renderLayout();
  }

  /**
   * Create wholesale customer account
   *
   * ADDED BY COBBLE HILL
   *
   */
  public function createWholesalePostAction()
  {
    $redirect = str_replace( '/','', $this->getRequest()->getParam('redirect_path'));
    $session = $this->_getSession();

    if ($session->isLoggedIn())
    {
      $this->_redirect($redirect);
      return;
    }

    $session->setEscapeMessages(true); // prevent XSS injection in user input

    if (!$this->getRequest()->isPost() )
    {
      $errUrl = $this->_getUrl($redirect, array('_secure' => true));
      $this->_redirectError($errUrl);
      return;
    }

    $customer = $this->_getCustomer();
    $data     = $this->getRequest()->getPost();

    try
    {
      $errors = $this->_getCustomerErrors($customer);

      if( empty($errors) )
      {
        $customer->cleanPasswordsValidationData();
        $customer->setData('group_id', '2' );
        $customer->save();

        if( $data['email'] != null )
        {
          $mchmp = new MailChimp('bc2904a2fce43bfcc06eab9bd1a36579-us4');

          $args = array(
            'id'                => '1b63c4a4cf',
            'email'             => array( 'email' => $data['email'] ),
            'merge_vars'        => array(),
            'email_type'        => 'html',
            'double_optin'      => true,
            'update_existing'   => true,
            'replace_interests' => true,
            'send_welcome'      => true,
          );

          $result = $mchmp->call('lists/subscribe', $args );
        }

        $this->_dispatchRegisterSuccess($customer);
        $this->_successProcessWholesaleRegistration($customer);




        return;
      }
      else
      {
        $this->_addSessionError($errors);
      }
    }
    catch (Mage_Core_Exception $e)
    {
      $session->setCustomerFormData($this->getRequest()->getPost());

      if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS)
      {
        $url = $this->_getUrl('customer/account/forgotpassword');
        $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
        $session->setEscapeMessages(false);
      }
      else
      {
        $message = $e->getMessage();
      }

      $session->addError($message);
    }
    catch (Exception $e)
    {
      $session->setCustomerFormData( $this->getRequest()->getPost())->addException($e, $this->__('Cannot save the customer.') );
    }

    $errUrl = $this->_getUrl($redirect, array('_secure' => true));
    $this->_redirectError($errUrl);
  }






  /**
     * Success Registration
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Customer_AccountController
     */
    protected function _successProcessWholesaleRegistration(Mage_Customer_Model_Customer $customer)
    {
        $session = $this->_getSession();

        if ( $customer->isConfirmationRequired() )
        {
            /** @var $app Mage_Core_Model_App */
            $app = $this->_getApp();
            /** @var $store  Mage_Core_Model_Store*/
            $store = $app->getStore();

            $customer->sendNewAccountEmail(
                'confirmation',
                $session->getBeforeAuthUrl(),
                $store->getId()
            );

            $customerHelper = $this->_getHelper('customer');
            $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
            $customerHelper->getEmailConfirmationUrl($customer->getEmail())));
            $url = $this->_getUrl('*/*/index', array('_secure' => true));

        }
        else
        {
            $session->setCustomerAsLoggedIn($customer);
            $url = $this->_welcomeWholesaleCustomer($customer);
        }


        $emailTemplateVariables = $admin_emails = $address_data = array();

        $emailTemplate = Mage::getModel('core/email_template')->loadDefault('user_registration_admin_notification_template');
        $data          = $customer->getData();
        $group_id      = (int) $customer->getGroupId();
        $groups        = Mage::getModel('customer/group')->load( $group_id );

        foreach( $customer->getAddresses() as $address )
        {
          $address_data[] = $address->toArray();
        }

        $region = Mage::getModel('directory/region')->load($address_data[0]['region_id']);

        $emailTemplateVariables['account_type']          = $groups->getCustomerGroupCode();
        $emailTemplateVariables['first_name']            = $customer->getFirstname();
        $emailTemplateVariables['last_name']             = $customer->getLastname();
        $emailTemplateVariables['email']                 = $customer->getEmail();
        $emailTemplateVariables['phone']                 = $address_data[0]['telephone'];
        $emailTemplateVariables['address']               = $address_data[0]['street'].', '.$address_data[0]['city'].' '.$region->getName().' '.$address_data[0]['postcode'];
        $emailTemplateVariables['user_title']            = $data['title'];
        $emailTemplateVariables['user_business_license'] = $data['license'];
        $emailTemplateVariables['heard_about']           = $data['findout'];
        $emailTemplateVariables['fax']                   = $data['faxnumber'];
        $emailTemplateVariables['license']               = $data['license'];

        //$admin_emails[] = ;
        //$admin_emails[] = 'contact@industrywest.com';
        $admin_emails[]   = 'tmwagner66@gmail.com';

        //Appending the Custom Variables to Template.
        $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);

        //Sending E-Mail to Customers.
        $mail = Mage::getModel('core/email');
        $mail->setToName('Industry West');
        $mail->setToEmail( Mage::getStoreConfig('trans_email/ident_custom1/email') );
        $mail->setBody($processedTemplate);
        $mail->setSubject('Subject : New account registered');
        $mail->setFromEmail('info@industrywest.com');
        $mail->setFromName('Industry West')->setType('html');

        try
        {
          $mail->send();
        }
        catch(Exception $error)
        {
          Mage::getSingleton('core/session')->addError( $error->getMessage() );
          return false;
        }

        $this->_redirectSuccess($url);
        return $this;
    }






  /**
     * Add welcome message and send new account email.
     * Returns success URL
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $isJustConfirmed
     * @return string
     */
    protected function _welcomeWholesaleCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false)
    {
        $this->_getSession()->addSuccess(
            $this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName())
        );
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType =  $this->_getHelper('customer/address')->getTaxCalculationAddressType();
            $userPrompt = '';
            switch ($configAddressType) {
                case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you shipping address for proper VAT calculation',
                        $this->_getUrl('customer/address/edit'));
                    break;
                default:
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you billing address for proper VAT calculation',
                        $this->_getUrl('customer/address/edit'));
            }
            $this->_getSession()->addSuccess($userPrompt);
        }

        //$customer->sendNewAccountEmail(  $isJustConfirmed ? 'confirmed' : 'registered', '', Mage::app()->getStore()->getId() );

        $customer->sendNewAccountEmail( 'new_wholesale',  '', Mage::app()->getStore()->getId() );

        $successUrl = $this->_getUrl('*/*/index', array('_secure' => true));
        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }



  /**
   * Create customer account action
   */
  public function createPostAction()
  {
    $session = $this->_getSession();

    if ($session->isLoggedIn())
    {
      $this->_redirect('*/*/');
      return;
    }

    $session->setEscapeMessages(true); // prevent XSS injection in user input

    if( $this->getRequest()->isPost() )
    {
      $errors = array();

      if( !$customer = Mage::registry('current_customer') )
      {
        $customer = Mage::getModel('customer/customer')->setId(null);
      }

      /* @var $customerForm Mage_Customer_Model_Form */
      $customerForm = Mage::getModel('customer/form');
      $customerForm->setFormCode('customer_account_create')->setEntity($customer);

      $customerData = $customerForm->extractData($this->getRequest());

      if ($this->getRequest()->getParam('is_subscribed', false))
      {
          $customer->setIsSubscribed(1);
      }

      /**
       * Initialize customer group id
       */
      if($this->getRequest()->getPost('group_id'))
 			{
			  $customer->setGroupId($this->getRequest()->getPost('group_id'));
 			}
 			else
 			{
			  $customer->getGroupId();
 			}

      if( $this->getRequest()->getPost('create_address') )
      {
          /* @var $address Mage_Customer_Model_Address */
          $address = Mage::getModel('customer/address');
          /* @var $addressForm Mage_Customer_Model_Form */
          $addressForm = Mage::getModel('customer/form');
          $addressForm->setFormCode('customer_register_address')
              ->setEntity($address);

          $addressData    = $addressForm->extractData($this->getRequest(), 'address', false);
          $addressErrors  = $addressForm->validateData($addressData);

          if ($addressErrors === true)
          {
              $address->setId(null)
                  ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                  ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
              $addressForm->compactData($addressData);
              $customer->addAddress($address);

              $addressErrors = $address->validate();

              if (is_array($addressErrors))
              {
                $errors = array_merge($errors, $addressErrors);
              }
          }
          else
          {
            $errors = array_merge($errors, $addressErrors);
          }
      }

      try
      {
          $customerErrors = $customerForm->validateData($customerData);

          if ($customerErrors !== true)
          {
            $errors = array_merge($customerErrors, $errors);
          }
          else
          {
              $customerForm->compactData($customerData);
              $customer->setPassword($this->getRequest()->getPost('password'));
              $customer->setConfirmation($this->getRequest()->getPost('confirmation'));
              $customerErrors = $customer->validate();

              if (is_array($customerErrors))
              {
                  $errors = array_merge($customerErrors, $errors);
              }
          }

          $validationResult = count($errors) == 0;

          if (true === $validationResult)
          {
              $customer->save();

              Mage::dispatchEvent('customer_register_success',
                  array('account_controller' => $this, 'customer' => $customer)
              );

              if ($customer->isConfirmationRequired())
              {
                  $customer->sendNewAccountEmail(
                      'confirmation',
                      $session->getBeforeAuthUrl(),
                      Mage::app()->getStore()->getId()
                  );

                  $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.',
                                       Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail()))
                                      );
                  $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
                  return;

              }
              else
              {
                  $session->setCustomerAsLoggedIn($customer);
                  $url = $this->_welcomeCustomer($customer);
                  $this->_redirectSuccess($url);
                  return;
              }



          }
          else
          {
              $session->setCustomerFormData($this->getRequest()->getPost());

              if (is_array($errors))
              {
                  foreach ($errors as $errorMessage)
                  {
                    $session->addError($errorMessage);
                  }
              }
              else
              {
                $session->addError($this->__('Invalid customer data'));
              }
          }
      }
      catch (Mage_Core_Exception $e)
      {
          $session->setCustomerFormData($this->getRequest()->getPost());
          if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS)
          {
              $url = Mage::getUrl('customer/account/forgotpassword');
              $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
              $session->setEscapeMessages(false);
          }
          else
          {
            $message = $e->getMessage();
          }
          $session->addError($message);

      }
      catch (Exception $e)
      {
          $session->setCustomerFormData($this->getRequest()->getPost())->addException($e, $this->__('Cannot save the customer.'));
      }
    }

    $this->_redirectError(Mage::getUrl('*/*/create', array('_secure' => true)));
  }

}
