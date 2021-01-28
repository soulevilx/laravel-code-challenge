<p align="center"><a href="https://aspireapp.com" target="_blank"><img src="https://global-uploads.webflow.com/5ed5b60be1889f546024ada0/5ed8a32c8e1f40c8d24bc32b_Aspire%20Logo%402x.png" width="400"></a></p>

## Aspire Challenge

### Setup procedure
1. Fork the repository in your personal github account
2. Checkout a new feature branch from `main`
3. Complete the test in 1h and 30min
4. Push the code and prepare the Pull Request from feature branch to main branch

### Test #01
#### Objective
Create feature tests to test *DebitCard* and *DebitCardTransaction* endpoints and relatives policies, validations and resources.

#### Business Logic
Each customer can have multiple *Debit Cards* and each debit card can have many *Debit Card Transactions*.

- The customer should be able to create, update, read and delete his debit cards. 
- For each debit card the customer should be able to read and create debit card transactions.

##### Debit cards endpoints:
- **get** `/debit-cards`
- **post** `/debit-cards`
- **get** `/debit-cards/{debitCard}`
- **put** `/debit-cards/{debitCard}`
- **delete** `/debit-cards/{debitCard}`

##### Debit card transactions endpoints *(optional/bonus point)*:
- **get** `/debit-card-transactions`
- **post** `/debit-card-transactions`
- **get** `/debit-card-transactions/{debitCardTransaction}`

For each endpoint there are specific condition and validation to asserts

#### Challenge
Read through the *DebitCard* and *DebitCardTransaction* routes, controllers, requests, resources and policies. 
Understand the logic and write as much tests as possible to validate the endpoints. The `DebitCardControllerTest` and `DebitCardTransactionTest` are already created you just need to complete them.

Tips:
- verify positive and negative scenarios
- assert response and database values
- customer can handle only his own debit cards

**IMPORTANT:** For this challenge you SHOULD ONLY update the feature tests

---

### Test #02

#### Objective
Create a Loan service to handle repayments based on complete unit tests already created.

#### Business Logic
Each customer can have a credit *loan* (due in 3 or 6 months). So a Loan has 3 or 6 *scheduled repayments* (once each month),
and it can be repaid with *received repayments*.
Example:

Loan of 3 months, amount 3000$, created on 2021-01-01
- Scheduled Repayment of 1000$ due to 2021-02-01
- Scheduled Repayment of 1000$ due to 2021-03-01
- Scheduled Repayment of 1000$ due to 2021-04-01

A customer can repay the full amount of each single scheduled repayment, but also he can repay partially or in full

#### Challenge
Read through the tests of LoanService to understand what is the logic to be implemented. All classes and files are already created, you just need to complete them.
In order to make the unit tests passed, you need to fulfil:
- the migrations/factories for scheduled_repayments and received_repayment tables (migration for loans table already done);
- the Loan, ScheduledRepayment, and ReceivedRepayment Models;
- the LoanService class;

**IMPORTANT:** For this challenge you SHOULD NOT update the unit test
