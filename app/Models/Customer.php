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

    public static function createCustomer(Request $request): Customer {
        self::validateCreateRequest($request);
        $customer = Customer::create($request->all());
        
        if (!$customer)
            throw new UnexpectedErrorException();

        $customer->emptyPasswordForDataProtection();

        return $customer;
    }

    public static function validateCreateRequest(Request $request): void {
        $requestData = $request->all();

        self::validateIfCustomerAlreadyExists($requestData);
    }

    private static function validateIfCustomerAlreadyExists(array $customerData): void {
        $customer = self::findByEmail($customerData['email']);

        if ($customer->id) {
            throw new CustomerAlreadyExistsException($customer);
        }
    }

    public static function findByEmail(string $email): Customer {
        $customer = DB::table('customers')
            ->where('email', '=', $email)
            ->first();

        $customer = Customer::hydrate([$customer])[0];
        $customer->emptyPasswordForDataProtection();

        return $customer;
    }

    public function emptyPasswordForDataProtection() {
        $this->password = "";
    }

    public static function updateCustomer(Request $request, Customer $customer) {
        $requestData = $request->all();
        $requestData = self::getRequestDataWithOriginalPasswordIfEmpty(
            $requestData, $customer->password);
        
        self::validateInmutableFieldsDidNotChange($requestData, $customer);
        $customer->update($requestData);
        $customer->emptyPasswordForDataProtection();

        return $customer;
    }

    private static function getRequestDataWithOriginalPasswordIfEmpty(
        array $requestData, string $originalPassword): array {
        
        if (key_exists('password', $requestData) && $requestData['password'] == "") {
            $requestData['password'] = $originalPassword;
        }

        return $requestData;
    }

    private static function validateInmutableFieldsDidNotChange(
        array $requestData, Customer $customer): void {

        if (key_exists('email', $requestData) && $requestData['email'] !== $customer->email) {
            throw new InvalidUpdateException();
        }
    }

    public static function findByIdOrFail(int $id): Customer {
        $customer = DB::table('customers')->find($id);

        if (!$customer)
            throw new NotFoundException();

        $customer = Customer::hydrate([$customer])[0];
        $customer->emptyPasswordForDataProtection();

        return $customer;
    }

    public static function findByEmailAndPasswordOrFail(
        string $email, string $password): Customer {

        $customer = DB::table('customers')
            ->where('email', $email)
            ->where('password', $password)
            ->first();

        if (!$customer)
            throw new NotFoundException();

        $customer = Customer::hydrate([$customer])[0];
        $customer->emptyPasswordForDataProtection();

        return $customer;
    }

    public function appendOrders(): void {
        $orders = Order::findOrdersByCustomerId($this->id);
        $this->orders = $orders;
    }

    public function appendPaidOrders(): void {
        $activeOrders = Order::findPaidOrdersByCustomerId($this->id);
        $activeOrders = Order::appendOrderLinesToOrdersArray($activeOrders);
        $this->orders = $activeOrders;
    }
}
