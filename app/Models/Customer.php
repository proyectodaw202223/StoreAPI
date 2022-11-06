<?php

namespace App\Models;

use App\Exceptions\CustomerAlreadyExistsException;
use App\Exceptions\InvalidUpdateException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnexpectedErrorException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Validates an HTTP POST request to determine if
     * a customer can be created with the given data.
     * 
     * @param Request $request The creation (HTTP POST) request to be validated.
     */
    public static function validateCreateRequest(Request $request): void {
        $requestData = $request->all();

        Customer::validateIfCustomerAlreadyExists($requestData);
    }

    /**
     * Throws a {@link CustomerAlreadyExistsException} if the customer already exists.
     * 
     * @param array $customerData An array with the customer data to be validated.
     */
    private static function validateIfCustomerAlreadyExists(array $customerData): void {
        $customer = self::findByEmail($customerData['email']);

        if ($customer->id) {
            throw new CustomerAlreadyExistsException($customer);
        }
    }

    /**
     * Finds and return the customer with the given email or an empty object.
     * 
     * @param string $email The given email to search for the customer.
     * @return Customer The customer with the given email.
     */
    public static function findByEmail(string $email): Customer {
        $customer = DB::table('customers')
            ->where('email', '=', $email)
            ->first();

        return Customer::hydrate([$customer])[0];
    }

    /**
     * Validates an HTTP PUT request to determine if
     * a customer can be updated with the given data.
     * 
     * @param Request $request The update (HTTP POST) request to be validated.
     * @param Customer $customer The customer that will be updated.
     */
    public static function validateUpdateRequest(Request $request, Customer $customer): void {
        $requestData = $request->all();

        Customer::validateInmutableFieldsDidNotChange($requestData, $customer);
    }

    /**
     * Validates that the inmutable fields of a customer did not change.
     * 
     * @param array An array with the customer data to be validated.
     * @param Customer $customer The customer that will be updated.
     */
    private static function validateInmutableFieldsDidNotChange(
        array $requestData, Customer $customer): void {

        if ($requestData['email'] !== $customer->email) {
            throw new InvalidUpdateException();
        }
    }

    /**
     * Finds and return the customer with the given id or an empty object.
     * 
     * @param int $id The given id to search for the customer.
     * @return Customer The customer with the given id.
     */
    public static function findByIdOrFail(int $id): Customer {
        $customer = DB::table('customers')->find($id);

        if (!$customer)
            throw new NotFoundException();

        return Customer::hydrate([$customer])[0];
    }

    /**
     * Finds and returns the customer with the given email and password or an empty object.
     * 
     * @param string $email The given email to search for the customer.
     * @param string $password The given password to search for the customer.
     * @return Customer The customer with the given email and password.
     */
    public static function findByEmailAndPasswordOrFail(string $email, string $password): Customer {
        $customer = DB::table('customers')
            ->where('email', $email)
            ->where('password', $password)
            ->first();

        if (!$customer)
            throw new NotFoundException();

        return Customer::hydrate([$customer])[0];
    }

    /**
     * Finds and appends the Orders related to the current
     * Customer as a new attribute named 'orders'.
     */
    public function appendOrders(): void {
        $orders = Order::findOrdersByCustomerId($this->id);
        $this->orders = $orders;
    }

    /**
     * Finds and appends the Paid Orders related to the current
     * Customer as a new attribute named 'orders'.
     */
    public function appendPaidOrders(): void {
        $activeOrders = Order::findPaidOrdersByCustomerId($this->id);
        $activeOrders = Order::appendOrderLinesToOrdersArray($activeOrders);
        $this->orders = $activeOrders;
    }
}
