<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventRegistrationRequest;
use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Illuminate\Support\Facades\Notification;

class EventController extends Controller
{
  public function get($eventId)
  {
    $event = Entry::find($eventId);
    return response()->json([
      'title' => $event->title,
      'has_salutation' => $event->has_salutation,
      'requires_salutation' => $event->requires_salutation,
      'has_name' => $event->has_name,
      'requires_name' => $event->requires_name,
      'has_firstname' => $event->has_firstname,
      'requires_firstname' => $event->requires_firstname,
      'has_email' => $event->has_email,
      'requires_email' => $event->requires_email,
      'has_phone' => $event->has_phone,
      'requires_phone' => $event->requires_phone,
      'has_company' => $event->has_company,
      'requires_company' => $event->requires_company,
      'has_location' => $event->has_location,
      'requires_location' => $event->requires_location,
      'has_address' => $event->has_address,
      'requires_address' => $event->requires_address,
      'has_meal_options' => $event->has_meal_options,
      'requires_meal_options' => $event->requires_meal_options,
      'meal_options' => [
        'Fleisch' => $event->has_meal_option_meat ? __('Fleisch') : null,
        'Fisch' => $event->has_meal_option_fish ? __('Fisch') : null,
        'Vegetarisch' => $event->has_meal_option_vegetarian ? __('Vegetarisch') : null,
        'Vegan' => $event->has_meal_option_vegan ? __('Vegan') : null,
      ],
      'has_button_additional_individuals' => $event->has_button_additional_individuals,
      'has_field_additional_individual_name' => $event->has_button_additional_individuals ? $event->has_field_additional_individual_name : false,
      'has_field_additional_individual_firstname' => $event->has_button_additional_individuals ? $event->has_field_additional_individual_firstname : false,
    ]);
  }

  public function register(Request $request)
  {
    $event = Entry::find($request->input('event_id'));

    $validationRules = $this->getValidationRules($event); 
    // $request->validate($validationRules['rules'], $validationRules['messages']);
    $validator = \Illuminate\Support\Facades\Validator::make(
      $request->all(),
      $validationRules['rules'],
      $validationRules['messages']
    );

    if ($validator->fails())
    {
      $errors = $validator->errors();
      $formattedErrors = [];

      foreach ($errors->messages() as $field => $messages)
      {
        if (strpos($field, 'additional_individuals.') === 0) {
          $parts = explode('.', $field);
          $index = $parts[1];
          $subfield = $parts[2];
          $formattedErrors['additional_individuals'][$index][$subfield] = $messages[0];
        } 
        else {
          $formattedErrors[$field] = $messages[0];
        }
      }

      return response()->json(['errors' => $formattedErrors], 422);
    }

    // $slug = $event->title . ' ' . $request->input('firstname') . ' ' . $request->input('name');
    

    // $slug = $event->title . ' ' . $request->input('firstname') . ' ' . $request->input('name');
    // $entry = Entry::make()
    // ->collection('requests_events')
    // ->slug($slug)
    // ->data(
    //   array_merge(
    //     [
    //       'title' => $event->title,
    //     ], 
    //     $request->validated()
    //   )
    // )
    // ->save();
      
    // Notification::route('mail', $request->input('email'))->notify(new GeneralUserEmail(
    //   $request->input('service'),
    //   $request->validated()
    // ));

    return response()->json(['message' => 'Store successful']);
  }

  protected function getValidationRules($event)
  {
    $validationRules = [];

    if ($event->has_salutation && $event->requires_salutation) {
      $validationRules['salutation'] = 'required';
    }

    if ($event->has_name && $event->requires_name) {
      $validationRules['name'] = 'required';
    }

    if ($event->has_firstname && $event->requires_firstname) {
      $validationRules['firstname'] = 'required';
    }

    if ($event->has_email && $event->requires_email) {
      $validationRules['email'] = 'required|email';
    } 

    if ($event->has_phone && $event->requires_phone) {
      $validationRules['phone'] = 'required';
    }

    if ($event->has_company && $event->requires_company) {
      $validationRules['company'] = 'required';
    }

    if ($event->has_location && $event->requires_location) {
      $validationRules['location'] = 'required';
    }

    if ($event->has_address && $event->requires_address) {
      $validationRules['address'] = 'required';
    }

    if ($event->has_meal_options && $event->requires_meal_options) {
      $validationRules['meal_options'] = 'required';
    }

    if ($event->has_button_additional_individuals) {
      $validationRules['additional_individuals.*.name'] = 'required';
      $validationRules['additional_individuals.*.firstname'] = 'required';
      
      if ($event->has_meal_options && $event->requires_meal_options) {
        $validationRules['additional_individuals.*.meal_options'] = 'required';
      }
    }

    // Set validation messages
    $validationMessages = [];

    if ($event->has_salutation && $event->requires_salutation) {
      $validationMessages['salutation.required'] = 'Anrede ist erforderlich';
    }

    if ($event->has_name && $event->requires_name) {
      $validationMessages['name.required'] = 'Name ist erforderlich';
    }

    if ($event->has_firstname && $event->requires_firstname) {
      $validationMessages['firstname.required'] = 'Vorname ist erforderlich';
    }

    if ($event->has_email && $event->requires_email) {
      $validationMessages['email.required'] = 'E-Mail-Adresse ist erforderlich';
      $validationMessages['email.email'] = 'E-Mail-Adresse muss gültig sein';
    }

    if ($event->has_phone && $event->requires_phone) {
      $validationMessages['phone.required'] = 'Telefonnummer ist erforderlich';
    }

    if ($event->has_company && $event->requires_company) {
      $validationMessages['company.required'] = 'Firma ist erforderlich';
    }
    
    if ($event->has_location && $event->requires_location) {
      $validationMessages['location.required'] = 'Ort ist erforderlich';
    }

    if ($event->has_address && $event->requires_address) {
      $validationMessages['address.required'] = 'Adresse ist erforderlich';
    }

    if ($event->has_meal_options && $event->requires_meal_options) {
      $validationMessages['meal_options.required'] = 'Essen ist erforderlich';
    }

    if ($event->has_button_additional_individuals) {
      $validationMessages['additional_individuals.*.name.required'] = 'Name ist erforderlich';
      $validationMessages['additional_individuals.*.firstname.required'] = 'Vorname ist erforderlich';

      if ($event->has_meal_options && $event->requires_meal_options) {
        $validationMessages['additional_individuals.*.meal_options.required'] = 'Essen ist erforderlich';
      }
    }


    return [
      'rules' => $validationRules,
      'messages' => $validationMessages,
    ];

  }
}