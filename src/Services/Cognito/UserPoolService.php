<?php

namespace Vng\EvaCore\Services\Cognito;

use Vng\EvaCore\Models\Professional;
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\Laravel\AwsFacade;
use Aws\Result;
use Exception;

class UserPoolService
{
    const DEFAULT_POOL_SETTINGS = [
        'AccountRecoverySetting' => [
            'RecoveryMechanisms' => [
                [
                    'Name' => 'verified_email',
                    'Priority' => 1,
                ]
            ],
        ],
        'AdminCreateUserConfig' => [
            'AllowAdminCreateUserOnly' => true,
            'InviteMessageTemplate' => [
                'EmailMessage' => 'Beste professional, er is een account voor je aangemaakt voor instrumentengids Eva met gebruikersnaam {username}. Uw tijdelijke wachtwoord is {####}',
                'EmailSubject' => 'Uitnodiging instrumentengids Eva',
                'SMSMessage' => 'Er is een account voor je aangemaakt voor instrumentengids Eva met gebruikersnaam {username}. Uw tijdelijke wachtwoord is {####}',
            ],
        ],
        'AutoVerifiedAttributes' => ['email'],
        'DeviceConfiguration' => [
            'ChallengeRequiredOnNewDevice' => false,
            'DeviceOnlyRememberedOnUserPrompt' => false,
        ],
        'EmailConfiguration' => [
//            'ConfigurationSet' => '<string>',
            'EmailSendingAccount' => 'DEVELOPER',
            'From' => 'professional@instrumentengids-eva.nl',
            'ReplyToEmailAddress' => 'no-reply@instrumentengids-eva.nl',
            'SourceArn' => 'arn:aws:ses:eu-west-1:163631682148:identity/professional@instrumentengids-eva.nl',
        ],
//        'EmailVerificationMessage' => 'Uw gebruikersnaam is {username} en uw tijdelijke wachtwoord is {####}',
//        'EmailVerificationSubject' => 'Uitnodiging Instrumentengids Eva',
//        'LambdaConfig' => [
//            'CreateAuthChallenge' => '<string>',
//            'CustomEmailSender' => [
//                'LambdaArn' => '<string>', // REQUIRED
//                'LambdaVersion' => 'V1_0', // REQUIRED
//            ],
//            'CustomMessage' => '<string>',
//            'CustomSMSSender' => [
//                'LambdaArn' => '<string>', // REQUIRED
//                'LambdaVersion' => 'V1_0', // REQUIRED
//            ],
//            'DefineAuthChallenge' => '<string>',
//            'KMSKeyID' => '<string>',
//            'PostAuthentication' => '<string>',
//            'PostConfirmation' => '<string>',
//            'PreAuthentication' => '<string>',
//            'PreSignUp' => '<string>',
//            'PreTokenGeneration' => '<string>',
//            'UserMigration' => '<string>',
//            'VerifyAuthChallengeResponse' => '<string>',
//        ],
        'MfaConfiguration' => 'OFF', // Migrate to 'ON'
        'Policies' => [
            'PasswordPolicy' => [
                'MinimumLength' => 8,
                'RequireLowercase' => true,
                'RequireNumbers' => true,
                'RequireSymbols' => false,
                'RequireUppercase' => true,
                'TemporaryPasswordValidityDays' => 1
            ],
        ],
        'PoolName' => null,
        'Schema' => [
            [
                'AttributeDataType' => 'DateTime',
                'Mutable' => true,
                'Name' => 'password_updated_at',
                'Required' => false,
            ],
            // ...
        ],
//        'SmsAuthenticationMessage' => 'Uw login code is {####}',
//        'SmsConfiguration' => [
//            'ExternalId' => '<string>',
//            'SnsCallerArn' => '<string>', // REQUIRED
//        ],
//        'SmsVerificationMessage' => 'Verifiëer uw telefoon met code {####}',
        'UsernameAttributes' => ['email'],
        'UsernameConfiguration' => [
            'CaseSensitive' => false,
        ],
//        'UserPoolAddOns' => [
//            'AdvancedSecurityMode' => 'OFF|AUDIT|ENFORCED', // REQUIRED
//        ],
//        'UserPoolTags' => ['<string>', ...],
        'VerificationMessageTemplate' => [
            'DefaultEmailOption' => 'CONFIRM_WITH_CODE',
            'EmailMessage' => 'Uw verificatiecode is {####}.',
            'EmailMessageByLink' => 'Klik op de onderstaande link om uw e-mailadres te verifiëren. {##Verifieer Email##}',
            'EmailSubject' => 'Verificatiecode Instrumentengids Eva',
            'EmailSubjectByLink' => 'Verificatielink Instrumentengids Eva',
//            'SmsMessage' => 'SMS Bericht voor wat?',
        ]
    ];

    public static function ensureUserPool(): void
    {
        $userPool = static::getUserPool();
        if (!is_null($userPool)) {
            static::updateUserPool($userPool);
            static::ensureUserPoolSchema($userPool);
        } else {
            static::createUserPool();
        }
    }

    protected static function createUserPool(): Result
    {
        /** @var CognitoIdentityProviderClient $cognitoClient */
        $cognitoClient = AwsFacade::createClient('CognitoIdentityProvider');
        return $cognitoClient->createUserPool(static::getUserPoolArgs());
    }

    protected static function updateUserPool(UserPoolModel $userPoolModel): Result
    {
        /** @var CognitoIdentityProviderClient $cognitoClient */
        $cognitoClient = AwsFacade::createClient('CognitoIdentityProvider');
        $args = static::getUserPoolArgs();
        $args['UserPoolId'] = $userPoolModel->getId();
        return $cognitoClient->updateUserPool($args);
    }

    protected static function getUserPoolArgs()
    {
        $args = static::DEFAULT_POOL_SETTINGS;
        $args['PoolName'] = static::getUserPoolName();
        $args['AdminCreateUserConfig']['InviteMessageTemplate']['EmailMessage'] = static::getInvitationEmail();
        $args['VerificationMessageTemplate']['EmailMessage'] = static::getValidationMessage();
        return $args;
    }

    public static function getUserPoolName()
    {
        $userPoolName = config('eva.userpool.name');
        if (is_null($userPoolName)) {
            throw new Exception('Userpool not setup in .env!');
        }
        return $userPoolName;
    }

    public static function getInvitationEmail()
    {
        $message = "Beste professional, <br><br>Er is een account voor je aangemaakt voor instrumentengids Eva.<br>";

        $url = config('eva.userpool.url');
        if (!is_null($url)) {
            $message .= "Je kan inloggen op <a href='". $url ."'>" . $url . "</a> om de instrumentengids te raadplegen over jullie instrumentenaanbod.";
        }

        $message .= "<br><br>
            Jouw gebruikersnaam is: {username}<br>
            Jouw tijdelijke wachtwoord is: {####}<br>
            <br>
            Let op: dit tijdelijke wachtwoord is slechts 24 uur geldig. Na de eerste keer inloggen wordt je meteen gevraagd je wachtwoord te wijzigen, daarna kan je al aan de slag!
            <br>
            Neem voor vragen contact op met je teamleider of de contactpersoon voor Eva binnen jullie gemeente.<br>
            <br>
            Veel succes met Eva!";
        return $message;
    }

    public static function getValidationMessage()
    {
        return "
        Beste professional,<br>
        <br>
        Er is een herstelcode om uw nieuwe wachtwoord in te stellen aangevraagd, deze heeft u nodig om uw wachtwoord te wijzigen: {####}<br>
        <br>
        Klik op “Stel wachtwoord opnieuw in” en vervolgens op “Ik heb al een herstelcode en wil mijn wachtwoord wijzigen”.<br>
        <br>
        Neem voor vragen contact op met je teamleider of de contactpersoon voor Eva binnen jullie gemeente.<br>
        <br>
        Veel succes met Eva!
        ";
    }

    public static function getUserPool(): ?UserPoolModel
    {
        return static::getUserPoolByName(static::getUserPoolName());
    }

    protected static function getUserPoolByName(string $name, string $nextToken = null): ?UserPoolModel
    {
        $result = static::listUserPools($nextToken);
        $userPools = $result['UserPools'];
        if (!count($userPools)) {
            return null;
        }

        $matchingPools = array_filter($userPools, fn ($pool) => $pool['Name'] === $name);
        if (empty($matchingPools)) {
            if ($result['NextToken']) {
                return static::getUserPoolByName($name, $result['NextToken']);
            }
            return null;
        }

        return UserPoolModel::create(reset($matchingPools));
    }

    protected static function listUserPools(string $nextToken = null): Result
    {
        /** @var CognitoIdentityProviderClient $cognitoClient */
        $cognitoClient = AwsFacade::createClient('CognitoIdentityProvider');

        $args = [
            'MaxResults' => 10,
        ];
        if (!is_null($nextToken)) {
            $args['NextToken'] = $nextToken;
        }
        return $cognitoClient->ListUserPools($args);
    }

    public static function describeUserPool(UserPoolModel $userPool): Result
    {
        /** @var CognitoIdentityProviderClient $cognitoClient */
        $cognitoClient = AwsFacade::createClient('CognitoIdentityProvider');
        return $cognitoClient->describeUserPool([
            'UserPoolId' => $userPool->getId()
        ]);
    }

    public static function resendConfirmationCode(Professional $professional): Result
    {
        /** @var CognitoIdentityProviderClient $cognitoClient */
        $cognitoClient = AwsFacade::createClient('CognitoIdentityProvider');
        return $cognitoClient->resendConfirmationCode([
            'Username' => $professional->email
        ]);
    }

    public static function ensureUserPoolSchema(UserPoolModel $userPool)
    {
        $missingAttributes = static::findMissingAttributes($userPool);
        if (count($missingAttributes) === 0) {
            return;
        }
        static::addCustomAttributes($userPool, $missingAttributes);
    }

    public static function findMissingAttributes(UserPoolModel $userPool): array
    {
        $args = static::getUserPoolArgs();
        $schema = $args['Schema'];
        $userPoolDescription = static::describeUserPool($userPool);
        $attributes = $userPoolDescription['UserPool']['SchemaAttributes'];
        $attributeNames = collect($attributes)->map(fn ($a) => $a['Name'])->toArray();
        return array_filter($schema, function ($schemaAttribute) use ($attributeNames) {
            return !in_array('custom:' . $schemaAttribute['Name'], $attributeNames);
        });
    }

    protected static function addCustomAttributes(UserPoolModel $userPool, array $attributesSchema): Result
    {
        /** @var CognitoIdentityProviderClient $cognitoClient */
        $cognitoClient = AwsFacade::createClient('CognitoIdentityProvider');
        return $cognitoClient->addCustomAttributes([
            'UserPoolId' => $userPool->getId(),
            'CustomAttributes' => $attributesSchema
        ]);
    }
}