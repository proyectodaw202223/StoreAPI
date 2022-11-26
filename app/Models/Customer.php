<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use DateTime;
use Exception;

use App\Exceptions\InvalidUpdateException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ResourceAlreadyExistsException;
use App\Exceptions\RestrictedDeletionException;
use App\Exceptions\UnexpectedErrorException;
use App\Exceptions\UpdateConflictException;

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
        $customerData = self::unsetOrdersFromCustomerData($customerData);
        self::validateCustomerDataOnCreate($customerData);
        $customer = Customer::create($customerData);
        
        if (!$customer)
            throw new UnexpectedErrorException();

        $customer->emptyPasswordForDataProtection();

        return $customer;
    }

    private static function unsetOrdersFromCustomerData(array $customerData): array {
        if (isset($customerData['orders']))
            unset($customerData['orders']);

        return $customerData;
    }

    public static function validateCustomerDataOnCreate(array $customerData): void {
        self::validateRequiredDataIsSetOnCreate($customerData);
        self::validateIfCustomerAlreadyExistsOnCreate($customerData);
    }

    private static function validateRequiredDataIsSetOnCreate(array $customerData): void {
        if (!isset($customerData['firstName']) ||
            !isset($customerData['email']) ||
            !isset($customerData['password'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateIfCustomerAlreadyExistsOnCreate(array $customerData): void {
        $customer = self::findByEmail($customerData['email']);

        if ($customer->id) {
            throw new ResourceAlreadyExistsException();
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
        $customerData = self::unsetOrdersFromCustomerData($customerData);
        $customerData = self::getCustomerDataWithOriginalPasswordIfEmpty($customerData, $customer);
        self::validateCustomerDataOnUpdate($customerData, $customer);
        $customer->update($customerData);
        $customer->emptyPasswordForDataProtection();

        return $customer;
    }

    private static function getCustomerDataWithOriginalPasswordIfEmpty(
        array $customerData, Customer $originalCustomer): array {
        
        if (key_exists('password', $customerData) && $customerData['password'] == "") {
            $customerData['password'] = $originalCustomer->password;
        }

        return $customerData;
    }

    public static function validateCustomerDataOnUpdate(array $customerData, Customer $customer) {
        self::validateRequiredDataIsSetOnUpdate($customerData);
        self::validateUpdateConflict($customerData, $customer);
        self::validateInmutableFieldsDidNotChange($customerData, $customer);
    }

    private static function validateRequiredDataIsSetOnUpdate(array $customerData): void {
        if (!isset($customerData['firstName']) ||
            !isset($customerData['email']) ||
            !isset($customerData['password']) ||
            !isset($customerData['updated_at'])) {
            throw new InvalidUpdateException();
        }
    }

    private static function validateUpdateConflict(array $customerData, Customer $customer): void {
        $currentUpdatedAt = new DateTime($customer['updated_at']);
        $requestUpdatedAt = new DateTime($customerData['updated_at']);

        if ($currentUpdatedAt > $requestUpdatedAt) {
            throw new UpdateConflictException();
        }
    }

    private static function validateInmutableFieldsDidNotChange(
        array $customerData, Customer $customer): void {

        if ($customerData['email'] !== $customer->email) {
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
