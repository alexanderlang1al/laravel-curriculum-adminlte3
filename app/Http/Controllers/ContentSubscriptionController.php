<?php

namespace App\Http\Controllers;

use App\ContentSubscription;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContentSubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $input = $this->validateRequest();

        $this->permissionCheck($input['subscribable_type'], $input['subscribable_id'], 'access');

        $subscriptions = ContentSubscription::where([
            'subscribable_type' => $input['subscribable_type'],
            'subscribable_id' => $input['subscribable_id'],
        ])->orderBy('order_id');

        if (request()->wantsJson()) {
            return ['message' => $subscriptions->with(['content'])->get()];
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $subscription_request = $this->validateRequest();
        $this->permissionCheck($subscription_request['subscribable_type'], $subscription_request['subscribable_id'], 'create');

        $order_id = $this->getMaxOrderId($subscription_request['subscribable_type'], $subscription_request['subscribable_id']);

        foreach ($subscription_request['content_id'] as $content_id) {
            $response = [];
            $exists = ContentSubscription::where('content_id', $content_id)
                ->where('subscribable_type', $subscription_request['subscribable_type'])
                ->where('subscribable_id', $subscription_request['subscribable_id'])
                ->first();
            if ($exists === null) {
                $response[] = ContentSubscription::create([
                    'content_id'        => $content_id,
                    'subscribable_type' => $subscription_request['subscribable_type'],
                    'subscribable_id'   => $subscription_request['subscribable_id'],
                    'sharing_level_id'  => 1, //todo: should be dynamic
                    'order_id'          => $order_id,
                    'visibility'        => 1,
                    'owner_id'          => auth()->user()->id,
                ]
                );
            }

            $order_id++;
        }

        return $response;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //first get existing data to later adjust order_id
        $subscription_request = $this->validateRequest();
        $this->permissionCheck($subscription_request['subscribable_type'], $subscription_request['subscribable_id'], 'create');

        $old_subscription = ContentSubscription::where('content_id', $subscription_request['content_id'])
            ->where('subscribable_type', $subscription_request['subscribable_type'])
            ->where('subscribable_id', $subscription_request['subscribable_id'])
            ->get()->first();
        // update order_id
        if ($request->has('order_id')) {
            if ($this->toggleOrderId($old_subscription, request('order_id'))) {
                $subscriptions = ContentSubscription::where([
                    'subscribable_type' => $subscription_request['subscribable_type'],
                    'subscribable_id'   => $subscription_request['subscribable_id'],
                ])->orderBy('order_id');

                if (request()->wantsJson()) {
                    return ['message' => $subscriptions->with(['content'])->get()];
                }
            }
        }
    }

    /**
     * Reset order_ids
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request)
    {
        //first get existing data to later adjust order_id
        $subscription_request = $this->validateRequest();
        $this->permissionCheck($subscription_request['subscribable_type'], $subscription_request['subscribable_id'], 'create');

        $reset_subscriptions = (new ContentSubscription)
            ->where('subscribable_type', $subscription_request['subscribable_type'])
            ->where('subscribable_id', $subscription_request['subscribable_id'])
            ->orderBy('created_at', 'ASC')
            ->get();

        if ($reset_subscriptions->count() > 1) {
            $i = 0;
            foreach ($reset_subscriptions as $subscription) {
                DB::table('content_subscriptions')
                    ->where('content_id', $subscription->content_id)
                    ->where('subscribable_type', $subscription->subscribable_type)
                    ->where('subscribable_id', $subscription->subscribable_id)
                    ->where('order_id', '=', $subscription->order_id)
                    ->update(['order_id' => $i]);
                $i++;
            }
        }

        $subscriptions = ContentSubscription::where([
            'subscribable_type' => $subscription_request['subscribable_type'],
            'subscribable_id'   => $subscription_request['subscribable_id'],
        ])->orderBy('order_id');

        if (request()->wantsJson()) {
            return ['message' => $subscriptions->with(['content'])->get()];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ContentSubscription  $contentSubscription
     * @return \Illuminate\Http\Response
     */
    public function destroy(ContentSubscription $contentSubscription)
    {
        //
    }

    protected function getMaxOrderId($subscribable_type, $subscribable_id)
    {
        $order_id = DB::table('content_subscriptions')
            ->where('subscribable_type', $subscribable_type)
            ->where('subscribable_id', $subscribable_id)
            ->max('order_id');

        return (is_numeric($order_id)) ? $order_id + 1 : 0;
    }

    /* protected function resetOrderIds( $subscribable_type, $subscribable_id, $order_id, $direction = 'down') //see  contentController@destroy
     {
         return (new ContentSubscription)
             ->where('subscribable_type', $subscribable_type)
             ->where('subscribable_id', $subscribable_id)
             ->where('order_id', '>', $order_id)
             ->update([
                 'order_id'=> DB::raw('order_id'. ( ($direction === 'down') ? '-1' : '+1') )
             ]);
     }*/

    protected function toggleOrderId($old_subscription, $new_order_id)
    {
        //fix for old subscriptions without order_id (multiple entries with 0)
        $check_subscriptions = (new ContentSubscription)
            ->where('subscribable_type', $old_subscription->subscribable_type)
            ->where('subscribable_id', $old_subscription->subscribable_id)
            ->where('order_id', '=', 0)->get();

        if ($check_subscriptions->count() > 1) {
            $i = 0;
            foreach ($check_subscriptions as $subscription) {
                DB::table('content_subscriptions')
                ->where('content_id', $subscription->content_id)
                ->where('subscribable_type', $subscription->subscribable_type)
                ->where('subscribable_id', $subscription->subscribable_id)
                ->where('order_id', '=', $subscription->order_id)
                ->update(['order_id' => $i]);
                $i++;
            }
        }

        // toggle order_ids of ContentSubscriptions
        $responseA = (new ContentSubscription)
            ->where('subscribable_type', $old_subscription->subscribable_type)
            ->where('subscribable_id', $old_subscription->subscribable_id)
            ->where('order_id', '=', $new_order_id)
            ->update(['order_id' => $old_subscription->order_id]);

        $responseB = (new ContentSubscription)
            ->where('content_id', $old_subscription->content_id)
            ->where('subscribable_type', $old_subscription->subscribable_type)
            ->where('subscribable_id', $old_subscription->subscribable_id)
            ->update(['order_id'=> $new_order_id]);

        if (($responseA == true) and ($responseB == true)) {
            if (request()->wantsJson()) {
                return ['message' => true];
            }
        }
    }

    protected function validateRequest()
    {
        return request()->validate([
            'content_id' => 'sometimes',
            'subscribable_type' => 'required',
            'subscribable_id' => 'required',
            'sharing_level_id' => 'sometimes',
            'order_id' => 'sometimes',
            'visibility' => 'sometimes',
        ]);
    }

    /**
     * check if user is owner of curricula if creation context is curricula
     *
     * @param $referenceable_type
     * @param $referenceable_id
     * @return mixed
     */
    private function permissionCheck($referenceable_type, $referenceable_id, $action = 'create')
    {
        abort_unless((\Gate::allows('content_'.$action) or
            \Gate::allows($referenceable_type.'_content_'.$action)), 403);

        $model = $referenceable_type::find($referenceable_id);

        return $model->isAccessible();
    }
}
