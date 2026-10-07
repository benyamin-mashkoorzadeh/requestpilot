<?php

namespace Database\Seeders;

use App\Data\AiAnalysis;
use App\Models\Inquiry;
use App\Services\InquiryRoutingService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoInquirySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Insert a varied dashboard demo without changing existing inquiries.
     */
    public function run(InquiryRoutingService $routingService): void
    {
        $referenceTime = now();

        foreach ($this->inquiries() as $data) {
            $analysis = new AiAnalysis(
                intent: $data['intent'],
                intentConfidence: $data['intent_confidence'],
                priority: $data['priority'],
                priorityConfidence: $data['priority_confidence'],
            );

            $createdAt = $referenceTime->copy()->subHours($data['hours_ago']);
            $reviewedAt = $this->reviewedAt($data, $createdAt);
            $finalIntent = $data['reviewed_intent'] ?? $analysis->intent;
            $finalPriority = $data['reviewed_priority'] ?? $analysis->priority;

            $inquiry = new Inquiry;
            $inquiry->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'message' => $data['message'],
                'intent' => $analysis->intent,
                'intent_confidence' => $analysis->intentConfidence,
                'priority' => $analysis->priority,
                'priority_confidence' => $analysis->priorityConfidence,
                'assigned_team' => $routingService->teamForIntent($finalIntent),
                'status' => $routingService->statusForPriority($finalPriority),
                'requires_review' => $reviewedAt === null
                    ? $routingService->requiresReview($analysis)
                    : false,
                'reviewed_intent' => $data['reviewed_intent'] ?? null,
                'reviewed_priority' => $data['reviewed_priority'] ?? null,
                'reviewed_at' => $reviewedAt,
            ]);
            $inquiry->created_at = $createdAt;
            $inquiry->updated_at = $reviewedAt ?? $createdAt;
            $inquiry->save();
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function reviewedAt(array $data, Carbon $createdAt): ?Carbon
    {
        if (! isset($data['reviewed_intent'], $data['reviewed_priority'], $data['reviewed_after_hours'])) {
            return null;
        }

        return $createdAt->copy()->addHours($data['reviewed_after_hours']);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function inquiries(): array
    {
        return [
            [
                'name' => 'Maya Chen',
                'email' => 'maya.chen@cedarstudio.example',
                'message' => 'We are planning next quarter and would like a comparison of your team plans for twelve people. There is no deadline; I am gathering options for our budget meeting.',
                'intent' => 'sales',
                'intent_confidence' => 0.28,
                'priority' => 'low',
                'priority_confidence' => 0.31,
                'reviewed_intent' => 'sales',
                'reviewed_priority' => 'low',
                'reviewed_after_hours' => 5,
                'hours_ago' => 18,
            ],
            [
                'name' => 'Jonas Weber',
                'email' => 'jonas@weber-works.example',
                'message' => 'Could someone send pricing for five additional seats and explain whether they can be added partway through our current term?',
                'intent' => 'sales',
                'intent_confidence' => 0.82,
                'priority' => 'normal',
                'priority_confidence' => 0.71,
                'hours_ago' => 29,
            ],
            [
                'name' => 'Priya Nair',
                'email' => 'priya.nair@harboranalytics.example',
                'message' => 'Our procurement review closes Friday. We need enterprise pricing, the security questionnaire, and contract terms for 180 users before then.',
                'intent' => 'sales',
                'intent_confidence' => 0.88,
                'priority' => 'high',
                'priority_confidence' => 0.79,
                'hours_ago' => 41,
            ],
            [
                'name' => 'Ethan Brooks',
                'email' => 'ethan@fieldstone-health.example',
                'message' => 'Our board is choosing a vendor in two hours and RequestPilot is the final option. Please confirm immediately whether 600 licenses can be provisioned today.',
                'intent' => 'sales',
                'intent_confidence' => 0.74,
                'priority' => 'urgent',
                'priority_confidence' => 0.32,
                'hours_ago' => 52,
            ],
            [
                'name' => 'Sofia Alvarez',
                'email' => 'sofia@brightpath.example',
                'message' => 'I am curious whether you offer nonprofit discounts. This is early research and we probably will not make a decision until next year.',
                'intent' => 'sales',
                'intent_confidence' => 0.91,
                'priority' => 'low',
                'priority_confidence' => 0.87,
                'hours_ago' => 68,
            ],
            [
                'name' => 'Marcus Reed',
                'email' => 'marcus@atlasretail.example',
                'message' => 'Our trial workspace keeps rejecting SSO setup. We are evaluating the product, but what I need now is help getting the login configuration working.',
                'intent' => 'sales',
                'intent_confidence' => 0.26,
                'priority' => 'normal',
                'priority_confidence' => 0.57,
                'reviewed_intent' => 'support',
                'reviewed_priority' => 'normal',
                'reviewed_after_hours' => 7,
                'hours_ago' => 83,
            ],
            [
                'name' => 'Leila Haddad',
                'email' => 'leila@northbridge.example',
                'message' => 'We expect to double our operations team next month and need a quote for 90 more seats before Tuesday so finance can reserve the budget.',
                'intent' => 'sales',
                'intent_confidence' => 0.29,
                'priority' => 'high',
                'priority_confidence' => 0.68,
                'hours_ago' => 96,
            ],
            [
                'name' => 'Noah Williams',
                'email' => 'noah@orbitfreight.example',
                'message' => 'We open a new distribution center tomorrow morning. Can your team confirm licensing and onboarding availability for 240 staff today?',
                'intent' => 'sales',
                'intent_confidence' => 0.93,
                'priority' => 'urgent',
                'priority_confidence' => 0.84,
                'hours_ago' => 111,
            ],
            [
                'name' => 'Amelia Foster',
                'email' => 'amelia@paperkite.example',
                'message' => 'When I have time, could you explain how to change the default notification sound? Nothing is broken and this can wait.',
                'intent' => 'support',
                'intent_confidence' => 0.86,
                'priority' => 'low',
                'priority_confidence' => 0.82,
                'hours_ago' => 127,
            ],
            [
                'name' => 'Luca Romano',
                'email' => 'luca@rivermark.example',
                'message' => 'The CSV import rejects one of our column headings. We can enter those few records manually, but I would appreciate help fixing the mapping.',
                'intent' => 'support',
                'intent_confidence' => 0.27,
                'priority' => 'normal',
                'priority_confidence' => 0.33,
                'reviewed_intent' => 'support',
                'reviewed_priority' => 'normal',
                'reviewed_after_hours' => 4,
                'hours_ago' => 142,
            ],
            [
                'name' => 'Aisha Grant',
                'email' => 'aisha@citygrove.example',
                'message' => 'Four project managers cannot publish reports after yesterday’s update. Drafting still works, but tomorrow’s client delivery depends on publishing being restored.',
                'intent' => 'support',
                'intent_confidence' => 0.78,
                'priority' => 'high',
                'priority_confidence' => 0.34,
                'hours_ago' => 156,
            ],
            [
                'name' => 'Oliver Schmidt',
                'email' => 'oliver@kernsystems.example',
                'message' => 'Nobody in our company can sign in and all warehouse dispatch screens are blocked. Orders are accumulating now with no workaround.',
                'intent' => 'support',
                'intent_confidence' => 0.96,
                'priority' => 'urgent',
                'priority_confidence' => 0.93,
                'hours_ago' => 171,
            ],
            [
                'name' => 'Grace Kim',
                'email' => 'grace@lumenlegal.example',
                'message' => 'Is there a keyboard shortcut for switching workspaces? I am updating our optional internal tips document later this month.',
                'intent' => 'support',
                'intent_confidence' => 0.89,
                'priority' => 'low',
                'priority_confidence' => 0.9,
                'hours_ago' => 185,
            ],
            [
                'name' => 'Theo Martin',
                'email' => 'theo@maplecraft.example',
                'message' => 'My saved view resets when I reopen the browser. The standard view works, so I can keep working, but please help me retain the filters.',
                'intent' => 'support',
                'intent_confidence' => 0.28,
                'priority' => 'normal',
                'priority_confidence' => 0.66,
                'hours_ago' => 199,
            ],
            [
                'name' => 'Fatima El-Sayed',
                'email' => 'fatima@solsticefoods.example',
                'message' => 'The production planning integration has stopped for all six factories. Staff cannot release today’s manufacturing runs and there is no manual fallback.',
                'intent' => 'support',
                'intent_confidence' => 0.25,
                'priority' => 'high',
                'priority_confidence' => 0.3,
                'reviewed_intent' => 'support',
                'reviewed_priority' => 'urgent',
                'reviewed_after_hours' => 2,
                'hours_ago' => 214,
            ],
            [
                'name' => 'Daniel Osei',
                'email' => 'daniel@skylineenergy.example',
                'message' => 'An access link appears to expose another customer’s project files to our users. We have disabled sharing, but the link is still active.',
                'intent' => 'support',
                'intent_confidence' => 0.94,
                'priority' => 'urgent',
                'priority_confidence' => 0.91,
                'hours_ago' => 228,
            ],
            [
                'name' => 'Emma Lind',
                'email' => 'emma@fjorddesign.example',
                'message' => 'Could you tell me where historical receipts are downloaded? I am organizing last year’s records and there is no time pressure.',
                'intent' => 'billing',
                'intent_confidence' => 0.85,
                'priority' => 'low',
                'priority_confidence' => 0.88,
                'hours_ago' => 243,
            ],
            [
                'name' => 'Henry Walsh',
                'email' => 'henry@oakline.example',
                'message' => 'Please update the billing contact on our account and resend this month’s invoice to the new finance address.',
                'intent' => 'billing',
                'intent_confidence' => 0.29,
                'priority' => 'normal',
                'priority_confidence' => 0.31,
                'reviewed_intent' => 'billing',
                'reviewed_priority' => 'normal',
                'reviewed_after_hours' => 6,
                'hours_ago' => 257,
            ],
            [
                'name' => 'Camila Torres',
                'email' => 'camila@granite-labs.example',
                'message' => 'The invoice total is €8,400 higher than our signed order form. Month-end closes in two days, so we need the discrepancy explained promptly.',
                'intent' => 'billing',
                'intent_confidence' => 0.92,
                'priority' => 'high',
                'priority_confidence' => 0.8,
                'hours_ago' => 272,
            ],
            [
                'name' => 'Nikhil Patel',
                'email' => 'nikhil@westportmedia.example',
                'message' => 'Our card was charged twice for the annual renewal and the duplicate has frozen our operating account. Please return the second payment today.',
                'intent' => 'billing',
                'intent_confidence' => 0.24,
                'priority' => 'urgent',
                'priority_confidence' => 0.32,
                'reviewed_intent' => 'refund',
                'reviewed_priority' => 'urgent',
                'reviewed_after_hours' => 3,
                'hours_ago' => 286,
            ],
            [
                'name' => 'Rose Bennett',
                'email' => 'rose@littleharbor.example',
                'message' => 'For future budgeting, what day of the month does our subscription renew and can the billing cycle be changed later?',
                'intent' => 'billing',
                'intent_confidence' => 0.87,
                'priority' => 'low',
                'priority_confidence' => 0.84,
                'hours_ago' => 301,
            ],
            [
                'name' => 'Yuki Tanaka',
                'email' => 'yuki@novaworks.example',
                'message' => 'The receipt lists our old company address. Could you issue a corrected document for our routine bookkeeping?',
                'intent' => 'billing',
                'intent_confidence' => 0.81,
                'priority' => 'normal',
                'priority_confidence' => 0.29,
                'hours_ago' => 315,
            ],
            [
                'name' => 'Arthur Mensah',
                'email' => 'arthur@greenrail.example',
                'message' => 'A failed payment notice is blocking renewal for thirty field accounts. Their access lasts until Thursday, but finance needs the card issue resolved before then.',
                'intent' => 'billing',
                'intent_confidence' => 0.9,
                'priority' => 'high',
                'priority_confidence' => 0.76,
                'hours_ago' => 329,
            ],
            [
                'name' => 'Clara Moreau',
                'email' => 'clara@meridiantravel.example',
                'message' => 'Every payment attempt is being captured but the balance never updates, so repeated charges are accumulating across hundreds of bookings right now.',
                'intent' => 'billing',
                'intent_confidence' => 0.95,
                'priority' => 'urgent',
                'priority_confidence' => 0.94,
                'hours_ago' => 344,
            ],
            [
                'name' => 'Hannah Cooper',
                'email' => 'hannah@willowbooks.example',
                'message' => 'I bought an add-on by mistake while exploring the settings. There is no rush, but could the purchase be reversed when convenient?',
                'intent' => 'refund',
                'intent_confidence' => 0.27,
                'priority' => 'low',
                'priority_confidence' => 0.3,
                'reviewed_intent' => 'refund',
                'reviewed_priority' => 'low',
                'reviewed_after_hours' => 8,
                'hours_ago' => 358,
            ],
            [
                'name' => 'Mateo Silva',
                'email' => 'mateo@redwoodcoffee.example',
                'message' => 'We accidentally paid the same invoice from two bank accounts. Please reimburse one of the completed payments.',
                'intent' => 'refund',
                'intent_confidence' => 0.9,
                'priority' => 'normal',
                'priority_confidence' => 0.75,
                'hours_ago' => 372,
            ],
            [
                'name' => 'Ines Dubois',
                'email' => 'ines@bluepeak.example',
                'message' => 'Could you explain why the usage charge on our statement is higher than expected? I need the breakdown for routine reconciliation, not a repayment.',
                'intent' => 'refund',
                'intent_confidence' => 0.26,
                'priority' => 'high',
                'priority_confidence' => 0.33,
                'reviewed_intent' => 'billing',
                'reviewed_priority' => 'normal',
                'reviewed_after_hours' => 5,
                'hours_ago' => 387,
            ],
            [
                'name' => 'Samuel Price',
                'email' => 'samuel@clearwater-events.example',
                'message' => 'A cancelled conference generated a £22,000 software purchase we can no longer use. Our cash-flow plan depends on the funds being returned today.',
                'intent' => 'refund',
                'intent_confidence' => 0.91,
                'priority' => 'urgent',
                'priority_confidence' => 0.86,
                'hours_ago' => 401,
            ],
            [
                'name' => 'Nora Jensen',
                'email' => 'nora@quietcorner.example',
                'message' => 'The workshop was postponed, so I would like the optional template pack refunded. Processing it sometime this month is fine.',
                'intent' => 'refund',
                'intent_confidence' => 0.84,
                'priority' => 'low',
                'priority_confidence' => 0.28,
                'hours_ago' => 416,
            ],
            [
                'name' => 'Ben Okafor',
                'email' => 'ben@sunrise-fitness.example',
                'message' => 'I cancelled yesterday and noticed the latest monthly payment had already completed. Please return that final payment.',
                'intent' => 'refund',
                'intent_confidence' => 0.88,
                'priority' => 'normal',
                'priority_confidence' => 0.73,
                'hours_ago' => 430,
            ],
            [
                'name' => 'Elena Rossi',
                'email' => 'elena@stonearch.example',
                'message' => 'A duplicate annual payment of €14,500 is affecting this week’s payroll allocation. We can operate today, but need the duplicate reversed before Friday.',
                'intent' => 'refund',
                'intent_confidence' => 0.93,
                'priority' => 'high',
                'priority_confidence' => 0.81,
                'hours_ago' => 445,
            ],
            [
                'name' => 'Victor Lam',
                'email' => 'victor@metroclinic.example',
                'message' => 'Refunds for 70 patients are failing and each retry creates another debit. The financial exposure is growing while we speak.',
                'intent' => 'refund',
                'intent_confidence' => 0.92,
                'priority' => 'urgent',
                'priority_confidence' => 0.31,
                'hours_ago' => 459,
            ],
            [
                'name' => 'Alice Murray',
                'email' => 'alice@meadowarts.example',
                'message' => 'Our seasonal project ends later this year. Please let me know how closing the workspace works; no action is needed yet.',
                'intent' => 'cancellation',
                'intent_confidence' => 0.83,
                'priority' => 'low',
                'priority_confidence' => 0.79,
                'hours_ago' => 474,
            ],
            [
                'name' => 'Karim Saleh',
                'email' => 'karim@artisancloud.example',
                'message' => 'Please turn off renewal for our analytics add-on at the end of the current monthly term. We will keep the main account.',
                'intent' => 'cancellation',
                'intent_confidence' => 0.91,
                'priority' => 'normal',
                'priority_confidence' => 0.77,
                'hours_ago' => 488,
            ],
            [
                'name' => 'Zoe Campbell',
                'email' => 'zoe@pineandco.example',
                'message' => 'Our contract renews in three business days. Legal has decided not to continue, so please confirm termination before the renewal is processed.',
                'intent' => 'cancellation',
                'intent_confidence' => 0.89,
                'priority' => 'high',
                'priority_confidence' => 0.82,
                'hours_ago' => 503,
            ],
            [
                'name' => 'Ravi Kapoor',
                'email' => 'ravi@kineticapps.example',
                'message' => 'The cancellation page loops back to sign-in, and a renewal will be charged in forty minutes. No administrator can stop it from the account.',
                'intent' => 'cancellation',
                'intent_confidence' => 0.23,
                'priority' => 'urgent',
                'priority_confidence' => 0.31,
                'reviewed_intent' => 'support',
                'reviewed_priority' => 'urgent',
                'reviewed_after_hours' => 1,
                'hours_ago' => 517,
            ],
            [
                'name' => 'Mei Wong',
                'email' => 'mei@smallhours.example',
                'message' => 'We may discontinue an unused sandbox in the autumn. Could you share the retention policy so we can plan, but please do not close it now.',
                'intent' => 'cancellation',
                'intent_confidence' => 0.78,
                'priority' => 'low',
                'priority_confidence' => 0.86,
                'hours_ago' => 532,
            ],
            [
                'name' => 'Peter Novak',
                'email' => 'peter@ironleaf.example',
                'message' => 'Please close the old contractor workspace before its next monthly renewal. The active company workspace should remain untouched.',
                'intent' => 'cancellation',
                'intent_confidence' => 0.28,
                'priority' => 'normal',
                'priority_confidence' => 0.72,
                'hours_ago' => 546,
            ],
            [
                'name' => 'Layla Hassan',
                'email' => 'layla@coastline-group.example',
                'message' => 'We are consolidating vendors next week. Please terminate 60 regional subscriptions before Monday so another annual term does not begin.',
                'intent' => 'cancellation',
                'intent_confidence' => 0.9,
                'priority' => 'high',
                'priority_confidence' => 0.78,
                'hours_ago' => 561,
            ],
            [
                'name' => 'George Evans',
                'email' => 'george@fairview-hotel.example',
                'message' => 'A former administrator scheduled renewal today and the charge is due within the hour. End the subscription now; ownership cannot access the old admin account.',
                'intent' => 'cancellation',
                'intent_confidence' => 0.86,
                'priority' => 'urgent',
                'priority_confidence' => 0.92,
                'hours_ago' => 576,
            ],
        ];
    }
}
