<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class EventRegistrationRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
   */
  public function rules(): array
  {
    return [
      'event_id' => 'required|string|max:255',
      'name' => 'required_if:name,true|string|max:255',
      'firstname' => 'required_if:firstname,true|string|max:255',
      'email' => 'required_if:email,true|email|max:255',
      'salutation' => 'required_if:salutation,true|string|max:255',
      'company' => 'required_if:company,true|string|max:255',
      'phone' => 'required_if:phone,true|string|max:255',
      'address' => 'required_if:address,true|string|max:255',
      'location' => 'required_if:location,true|string|max:255',
    ];
  }

  public function messages(): array
  {
    return [
      'event_id.required' => 'Event-ID ist erforderlich',
      'event_id.string' => 'Event-ID ist erforderlich',
      'name.required_if' => 'Name ist erforderlich',
      'name.string' => 'Name ist erforderlich',
      'firstname.required_if' => 'Vorname ist erforderlich',
      'firstname.string' => 'Vorname ist erforderlich',
      'email.required_if' => 'E-Mail-Adresse ist erforderlich',
      'email.email' => 'E-Mail-Adresse muss gültig sein.',
      'salutation.required_if' => 'Anrede ist erforderlich',
      'salutation.string' => 'Anrede ist erforderlich',
      'company.required_if' => 'Firma ist erforderlich',
      'company.string' => 'Firma ist erforderlich',
      'phone.required_if' => 'Telefonnummer ist erforderlich',
      'phone.string' => 'Telefonnummer ist erforderlich',
      'address.required_if' => 'Adresse ist erforderlich',
      'address.string' => 'Adresse ist erforderlich',
      'location.required_if' => 'Ort ist erforderlich',
      'location.string' => 'Ort ist erforderlich',
    ];
  }
}