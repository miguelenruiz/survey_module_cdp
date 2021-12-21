<?php
/**
 * @file
 * Contains \Drupal\form_example_cdp_survey\Form\FormSurvey.
 */
namespace Drupal\form_example_cdp_survey\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Database;

use GuzzleHttp\httpClient;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\Component\Render\FormattableMarkup;

 
class FormSurvey extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'survey_example';
  }
 
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Name:'),      
      '#required' => TRUE,
    );

    $form['last_name'] = array(
      '#type' => 'textfield',
      '#title' => t('Last name:'),      
      '#required' => TRUE,
    );
   
    $form['email'] = array(
      '#type' => 'email',
      '#title' => $this->t('Email:'),
      // '#pattern' => '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$',
      '#required' => TRUE,
    );

    $form['phone'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Phone:'),
      // '#pattern' => '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$',
      '#required' => TRUE,
    );
 
    $form['respuestauno'] = array(
        '#type' => 'textfield',
        '#title' => t('Como te llamas'),        
        // '#required' => TRUE,
      );
  
      $form['respuestados'] = array(
        '#type' => 'textfield',
        '#title' => t('Como es tu apellido'),        
        // '#required' => TRUE,
      );
  
      $form['respuestatres'] = array(
        '#type' => 'textfield',
        '#title' => t('Como es tu email'),        
        // '#required' => TRUE,
      );

       // // CheckBoxes.
    $form['terminos'] = array(
        '#required' => TRUE,
        '#title' => 'He leído, entendido y aceptado los terminos',
        '#type' => 'checkbox',
        // '#attributes' => array('class' => array('input-control-checkbox'), 'id' => array('terminos')),
  
  
      );
  
      $form['marketing'] = array(
        '#type' => 'checkbox',
        '#title' => 'Acepto recibir información comercial y promociones de Cerveza Aguila.',
        // '#attributes' => array('class' => array('input-control-checkbox'), 'id' => array('advertising')),
      );
  
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );
    return $form;
  }
 
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $conn = Database::getConnection();
    $conn->insert('survey_example')->fields(
      array(
        'name' => $form_state->getValue('name'),
        'email' => $form_state->getValue('email'),
        'pregunta1' => $form_state->getValue('respuestauno'),
        'pregunta2' => $form_state->getValue('respuestados'),
        'pregunta3' => $form_state->getValue('respuestatres'),
        'terminos' => $form_state->getValue('terminos'),
        'marketing' => $form_state->getValue('marketing'),
      )
    )->execute();
    // $url = Url::fromRoute('hello.thankyou');
    // $form_state->setRedirectUrl($url);


    // evio al cdp 
    $tcpp =  $form_state->getValue('terminos');
    $marketing =  $form_state->getValue('marketing');

    // define variable that will be used to tell the __sendTD method if it should send to the production database
    $is_production = false;

    // define the purpose variable as an empty array
    $purposes = array();

    // check whether the TC-PP checkbox is checked, and if it is, then adds it to the purpose array - informed
    if ($tcpp) $purposes[] = 'TC-PP';

    // check whether the MARKETING-ACTIVATION checkbox is checked, and if it is, then adds it to the purpose array
    if ($marketing) $purposes[] = 'MARKETING-ACTIVATION';

    // here it's possible to add additional purposes to the purpose array

    // runs the __sendTD method with parameters got from the request, it should be changed based on your form fields, country, brand, campaign, form, and whether if it's running in the production environment or not
    $tdstatus = $this->__sendTD(
      array(
        "abi_name" => $form_state->getValue('name'),
        "abi_email" => $form_state->getValue('email'),
        "purpose_name" => $purposes,
      ),        // form data & purposes
      'col',          // country
      'TestSurvey',       // brand
      "TEST_SURVEYCDP_11_2021",           // campaign
      "TEST_SURVEYCDP_11_2021",   // form
      true,   // unify
      $is_production  // production flag
    );

    $resp =  $form_state->getValue('respuestauno');
    $resp2 = $form_state->getValue('respuestados');
    $resp3 = $form_state->getValue('respuestatres');
    $email = $form_state->getValue('email');
    $name = $form_state->getValue('name');
    $phone = $form_state->getValue('phone');
    $last_name = $form_state->getValue('last_name');

    $this->__sendSurvey($resp, $resp2 ,$resp3 ,$email ,$name);

   }

  public function validateForm(array &$form, FormStateInterface $form_state) {
   

    // espacio para validaciones del formulario 
 
  }
  public function __sendTD($form_data, $country, $brand, $campaign, $form, $unify, $production)
  {

    $td_env = $production ? 'prod' : 'dev';

    $http_protocol = isset($_SERVER['https']) ? 'https://' : 'http://';

    $form_data['abi_brand'] = $brand;
    $form_data['abi_campaign'] = $campaign;
    $form_data['abi_form'] = $form;
    $form_data['td_unify'] = $unify;
    $form_data['td_import_method'] = 'postback-api-1.2';
    $form_data['td_client_id'] = $_COOKIE['_td'];
    $form_data['td_url'] = $http_protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $form_data['td_host'] = $_SERVER['HTTP_HOST'];

    $td_country = $country;

    $td_apikey = $td_env !== 'prod' ? '9648/41e45454b77308046627548e0b4fe2ddbc0893d2' : '9648/ae3b5d347f6812326d350960264f62498678c6d1';

    $country_zone_mapping = array("nga" => "africa", "zwe" => "africa", "zaf" => "africa", "aus" => "apac", "chn" => "apac", "ind" => "apac", "jpn" => "apac", "kor" => "apac", "tha" => "apac", "vnm" => "apac", "bel" => "eur", "fra" => "eur", "deu" => "eur", "ita" => "eur", "nld" => "eur", "rus" => "eur", "esp" => "eur", "ukr" => "eur", "gbr" => "eur", "col" => "midam", "dom" => "midam", "ecu" => "midam", "slv" => "midam", "gtm" => "midam", "hnd" => "midam", "mex" => "midam", "pan" => "midam", "per" => "midam", "can" => "naz", "usa" => "naz", "arg" => "saz", "bol" => "saz", "bra" => "saz", "chl" => "saz", "ury" => "saz");

    $td_zone = $country_zone_mapping[$td_country];
    $curl = curl_init();

    $curl_opts = array(
      CURLOPT_URL => "https://in.treasuredata.com/postback/v3/event/{$td_zone}_source/{$td_country}_web_form",
      CURLOPT_POST => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        "X-TD-Write-Key: {$td_apikey}"
      ),
      CURLOPT_POSTFIELDS => json_encode($form_data)
    );

    curl_setopt_array($curl, $curl_opts);

    $response = @curl_exec($curl);
    $response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    return $response_code;
  }
 
// Esta funcion permite enviar cada pregunta con su respuesta al CDP
  public function __sendSurvey($resp, $resp2 , $resp3 ,$email ,$name) {
  // Validar cual tecnologia es mas apropiada para enviar la data  esto es solo un ejemplo  
  // puede utilizar glu
  // valida si respuesta uno tiene algo y hace la peticion curl
    if(!empty($resp) || $resp != NULL ){ 
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://in.treasuredata.com/postback/v3/event/midam_source/col_survey_responses_test",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => '{
            "abi_dateofbirth": "'.$dateofbirth.'",
            "abi_email": "'.$email.'",
            "abi_phone": "'.$phone.'",
            "abi_first_name": "'.$name.'",
            "abi_last_name": "'.$last_name.'",
            "abi_gender": "'.$gender.'",
            "abi_survey_id": "TEST_SURVEYCDP_11_2021",
            "abi_survey_title": "TEST_SURVEYCDP_11_2021",
            "abi_question": "¿Cual es tu cerveza favorita ?",
            "abi_response": "'.$resp.'",
            "abi_earned_points": 0,
            "abi_program_name": "Brand"
        }',
        CURLOPT_HTTPHEADER => array(
            "accept: */*",
            "cache-control: no-cache",
            "content-type: application/json",
            "postman-token: 31f53218-18a1-e564-e273-500a2179b602",
            "user-agent: Thunder Client (https://www.thunderclient.io)",
            "x-td-write-key: 9648/ae3b5d347f6812326d350960264f62498678c6d1"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
        echo "cURL Error #:" . $err;
        } else {
        echo $response;
        }
    }
    // valida si respuesta 2 tiene algo y hace la peticion curl

    if(!empty($resp2) || $resp2 != NULL ){
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://in.treasuredata.com/postback/v3/event/midam_source/col_survey_responses_test",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => '{
            "abi_dateofbirth": "'.$phone.'",
            "abi_email": "'.$email.'",
            "abi_phone": "'.$phone.'",
            "abi_first_name": "'.$name.'",
            "abi_last_name": "'.$last_name.'",
            "abi_gender": "'.$gender.'",
            "abi_survey_id": "TEST_SURVEYCDP_11_2021",
            "abi_survey_title": "TEST_SURVEYCDP_11_2021",
            "abi_question": " ¿ Donde te gusta tomar cerveza ?",
            "abi_response": "'.$resp2.'",
            "abi_earned_points": 0,
            "abi_program_name": "Brand"
        }',
        CURLOPT_HTTPHEADER => array(
            "accept: */*",
            "cache-control: no-cache",
            "content-type: application/json",
            "postman-token: 31f53218-18a1-e564-e273-500a2179b602",
            "user-agent: Thunder Client (https://www.thunderclient.io)",
            "x-td-write-key: 9648/ae3b5d347f6812326d350960264f62498678c6d1"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
        echo "cURL Error #:" . $err;
        } else {
          echo $response;
  
        }
       
    }

    // valida si respuesta 3 tiene algo y hace la peticion curl
    if(!empty($resp3) || $resp3 != NULL ){
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://in.treasuredata.com/postback/v3/event/midam_source/col_survey_responses_test",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => '{
            "abi_dateofbirth": "'.$dateofbirth.'",
            "abi_email": "'.$email.'",
            "abi_phone": "'.$phone.'",
            "abi_first_name": "'.$name.'",
            "abi_last_name": "'.$last_name.'",
            "abi_gender": "'.$gender.'",
            "abi_survey_id": "TEST_SURVEYCDP_11_2021",
            "abi_survey_title": "TEST_SURVEYCDP_11_2021",
            "abi_question": "¿Donde compras cerveza ?",
            "abi_response": "'.$resp3.'",
            "abi_earned_points": 0,
            "abi_program_name": "Brand"
        }',
        CURLOPT_HTTPHEADER => array(
            "accept: */*",
            "cache-control: no-cache",
            "content-type: application/json",
            "postman-token: 31f53218-18a1-e564-e273-500a2179b602",
            "user-agent: Thunder Client (https://www.thunderclient.io)",
            "x-td-write-key: 9648/ae3b5d347f6812326d350960264f62498678c6d1"
        ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
        echo "cURL Error #:" . $err;
        } else {
        echo $response;
        }


    }
  }
}


