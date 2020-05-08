<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>
<?php
class QulatricsAPIJsonTemplates extends BaseModel
{

    /* Constants ************************************************/

    /* API calls */
    const authenticator = '{
                "Type": "Authenticator",
                "FlowID": "",
                "PanelData": {
                    "LibraryID": "",
                    "PanelID": "",
                    "Type": "Expression",
                    "LogicType": "Panel"
                },
                "FieldData": {
                    "0": {
                        "0": {
                            "PanelField": "RecipientExternalDataReference",
                            "fieldLabel": "",
                            "AutoFill": 1,
                            "embeddedDataField": "code",
                            "Type": "Expression",
                            "LogicType": "EmbeddedField",
                            "Description": "<span class=\"ConjDesc\">If</span>  <span class=\"LeftOpDesc\">undefined</span> <span class=\"OpDesc\">#</span> "
                        },
                        "Type": "If"
                    },
                    "Type": "BooleanExpression"
                },
                "AutoAuthenticator": true,
                "FilterDataFields": true,
                "SSOOptions": {
                    "respondentMap": [],
                    "Type": "Token",
                    "token": {
                        "EncryptionMethod": "3DES",
                        "MacMethod": "MD5",
                        "Leeway": 300
                    },
                    "ldap": {
                        "Hostname": "ldap://",
                        "Port": "389",
                        "EmailField": "mail",
                        "Filter": "(sAMAccountName=%1)",
                        "FirstNameField": "givenname",
                        "LastNameField": "sn",
                        "ExternalDataReferenceField": ""
                    },
                    "UsePanel": "Yes",
                    "CaptureRespondentInfo": "No",
                    "UseSSO": "",
                    "cas": []
                },
                "Options": {
                    "maxAttempts": "100",
                    "questionText": {
                        "SystemMessage": {
                            "Section": "ErrorCodes",
                            "Message": "EAUTH04"
                        }
                    },
                    "authenticationError": {
                        "SystemMessage": {
                            "Section": "ErrorCodes",
                            "Message": "EAUTH02"
                        }
                    },
                    "failedAuthenticationError": {
                        "SystemMessage": {
                            "Section": "ErrorCodes",
                            "Message": "EAUTH05"
                        }
                    },
                    "allowRetake": true,
                    "loadExistingSession": false,
                    "reauthenticateSession": false
                }
            }';
}
?>