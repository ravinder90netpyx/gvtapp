<?php

namespace App\Jobs;

use App\Models\API_Response;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class WhatsappAPI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $destination;
    protected $message;
    protected $org_id;
    protected $template;
    protected $je_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($destination,$message, $org_id, $template, $category, $je_id =null)
    {
        $this->destination = $destination;
        $this->message = $message;
        $this->org_id = $org_id;
        $this->template = $template;
        $this->je_id = $je_id;
        $this->category = $category;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){
        // echo $this->destination; exit;
        // $api = array(
        //     'whatsapp_api_url' => 'https://api.gupshup.io/wa/api/v1/template/msg',
        //     'channel' => 'whatsapp',
        //     'src_name' => 'GVTGH9',
        //     'type' => 'document'
        //     // 'template_id' => 'c376f4e4-2743-4eb9-8cdb-2648f7457d22',
        // );

        $destination = $this->destination;
        $message = $this->message;
        $org_id = $this->org_id;
        $template = $this->template;
        $je_id = $this->je_id ?? null;
        $category = $this->category ?? '';

        $model = new \App\Models\Organization_Settings();
        $group = 'whatsapp_settings';

        $url = $model->getVal($group, 'api_url', $org_id);
        $channel = $model->getVal($group, 'channel', $org_id);
        $src_name = $model->getVal($group, 'src_name', $org_id);
        $src_no = $model->getVal($group, 'source_number',$org_id);
        $api_key = $model->getVal($group, 'api_key',$org_id);
        $curl = curl_init();

        $post_data = [];

        $post_data['channel'] = $channel;
        $post_data['source'] = $src_no;
        $post_data['destination'] = $destination;
        $post_data['src.name'] = $src_name;
        $post_data['template'] = $template;
        $post_data['message'] = $message;

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => Arr::query($post_data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Apikey:'.$api_key
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        echo $response;

            $res_arr = json_decode($response);
            // dd($res_arr->status);
            if($res_arr->status == 'submitted'){
                $api_arr = [];
                $api_arr['journal_entry_id'] = !empty($je_id) ? $je_id: null;
                $api_arr['response'] = $response;
                $api_arr['category'] = !empty($category) ? $category: null;
                if(!empty($je_id)){
                    $je_model = \App\Models\Journal_Entry::find($je_id);
                    $count = $je_model->count ?? 0;
                    $count++;
                    $upd = $je_model->update(['count' => $count]);
                }
                $model = new API_Response();
                $model->create($api_arr);
            }
    }
}
