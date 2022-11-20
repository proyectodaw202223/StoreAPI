<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Exception;

use App\Exceptions\CustomerAlreadyExistsException;
use App\Exceptions\InvalidUpdateException;
use App\Exceptions\NotFoundException;
use App\Exceptions\RestrictedDeletionException;
use App\Exceptions\UnexpectedErrorException;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public static function createCustomer(array $customerData): Customer {
        self::validateCustomerDataForCreation($customerData);
        $customer = Customer::create($customerData);
        
        if (!$customer)
            throw new UnexpectedErrorException();

        $customer->emptyPasswordForDataProtection();

        return $customer;
    }

    public static function validateCustomerDataForCreation(array $customerData): void {
        self::validateIfCustomerAlreadyExists($customerData);
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

    public static function updateCustomer(array $customerData, Customer $customer) {
        $customerData = self::getCustomerDataWithOriginalPasswordIfEmpty(
            $customerData, $customer->password);
        
        self::validateCustomerDataForUpdate($customerData, $customer);
        $customer->update($customerData);
        $customer->emptyPasswordForDataProtection();

        return $customer;
    }

    private static function getCustomerDataWithOriginalPasswordIfEmpty(
        array $customerData, string $originalPassword): array {
        
        if (key_exists('password', $customerData) && $customerData['password'] == "") {
            $customerData['password'] = $originalPassword;
        }

        return $customerData;
    }

    public static function validateCustomerDataForUpdate(array $customerData, Customer $customer) {
        self::validateInmutableFieldsDidNotChange($customerData, $customer);
    }

    private static function validateInmutableFieldsDidNotChange(
        array $customerData, Customer $customer): void {

        if (key_exists('email', $customerData) && $customerData['email'] !== $customer->email) {
            throw new InvalidUpdateException();
        }
    }

    public static function deleteCustomer(Customer $customer): void {
        try {
            $customer->delete();
        } catch (Exception $e) {
            throw new RestrictedDeletionException();
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

    public static function findById(int $id): Customer {
        $customer = DB::table('customers')->find($id);
        $customer = Customer::hydrate([$customer])[0];
        $customer->emptyPasswordForDataProtection();

        return $customer;
    }

    public static function findByEmailAndPasswordOrFail(
        string $email, string $password): Customer {

        $customer = DB::table('customers')
            ->where('email', '=', $email)
            ->where('password', '=', $password)
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
        $paidOrders = Order::findPaidOrdersByCustomerId($this->id);

        if (count($paidOrders) > 0)
            $paidOrders = Order::appendOrderLinesToOrdersArray($paidOrders);

        $this->orders = $paidOrders;
    }

    public function appendCreatedOrder(): void {
        $orders = [];
        $order = Order::findCreatedOrderByCustomerId($this->id);
        
        if ($order->id) {
            $order->appendOrderLines();
            array_push($orders, $order);
        }

        $this->orders = $orders;
    }
}
