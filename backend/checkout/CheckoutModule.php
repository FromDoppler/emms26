<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/backend/checkout/CheckoutRequires.php');

class CheckoutModule
{
    public static function createCalculateCheckoutService(): CalculateCheckoutUseCase
    {
        $db = self::newDb();

        return new CalculateCheckoutUseCase(
            self::createEventContextResolver(),
            self::createPricingService($db),
            new RegisteredProfileRepository($db)
        );
    }

    public static function createCreateCheckoutService(): CreateCheckoutUseCase
    {
        $db = self::newDb();
        $transactions = new CheckoutTransactionsRepository($db);
        $pricingService = self::createPricingService($db);
        $providerClient = new DopplerPaymentsApiClient(new SuperUserJwtService());
        $eligibilityService = new CheckoutEligibilityService(new RegisteredProfileRepository($db));
        $responseFactory = new CheckoutResponseFactory();
        $completion = self::createCompletionService($db, $transactions);
        $paymentProcessor = new CheckoutPaymentProcessor(
            $transactions,
            $providerClient,
            $completion,
            function (): array {
                $freshDb = CheckoutModule::newDb();
                $freshTransactions = new CheckoutTransactionsRepository($freshDb);
                return [
                    'transactions' => $freshTransactions,
                    'completion' => CheckoutModule::createCompletionService($freshDb, $freshTransactions),
                ];
            },
            $responseFactory
        );
        $failureHandler = new CheckoutFailureHandler(function (): DB {
            return CheckoutModule::newDb();
        }, $responseFactory);
        $eventContextResolver = self::createEventContextResolver();

        return new CreateCheckoutUseCase(
            $pricingService,
            $transactions,
            $eligibilityService,
            $eventContextResolver,
            $paymentProcessor,
            $failureHandler,
            $responseFactory
        );
    }

    public static function createGetCheckoutService(): GetCheckoutUseCase
    {
        return new GetCheckoutUseCase(
            new CheckoutTransactionsRepository(self::newDb()),
            new CheckoutResponseFactory()
        );
    }

    public static function createEventContextResolver(): CheckoutEventContextResolver
    {
        return new CheckoutEventContextResolver();
    }

    private static function createPricingService(DB $db): CheckoutPricingService
    {
        $coupons = new CheckoutCouponsRepository($db);
        $couponService = new CheckoutCouponService($coupons);
        $tickets = new CheckoutTicketsRepository($db);

        return new CheckoutPricingService($tickets, $couponService);
    }

    public static function createCompletionService(DB $db, CheckoutTransactionsRepository $transactions): PostCheckoutService
    {
        return new PostCheckoutService(
            $db,
            $transactions,
            new RegisteredProfileRepository($db),
            new VipAccessService($db),
            new UserEventJobCreator(new UserEventJobsRepository($db)),
            function (): InlineUserEventJobRunner {
                return new InlineUserEventJobRunner(
                    new UserEventJobsRepository(CheckoutModule::newDb()),
                    new UserEventJobHandlerRegistry([
                        new EmailSendJobHandler(),
                        new SpreadsheetSaveJobHandler(),
                        new DopplerListAddJobHandler(),
                    ])
                );
            },
            new PostCheckoutUserEventsFactory()
        );
    }

    private static function newDb(): DB
    {
        return new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    }
}
