<?php

namespace App\Jobs;

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

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($destination,$message, $org_id, $template)
    {
        $this->destination = $destination;
        $this->message = $message;
        $this->org_id = $org_id;
        $this->template = $template;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){
        // echo $this->destination; exit;
        $api = array(
            'whatsapp_api_url' => 'https://api.gupshup.io/wa/api/v1/template/msg',
            'channel' => 'whatsapp',
            'src_name' => 'GVTGH9',
            'type' => 'document'
            // 'template_id' => 'c376f4e4-2743-4eb9-8cdb-2648f7457d22',
        );

        $destination = $this->destination;
        $message = $this->message;
        $org_id = $this->org_id;
        $template = $this->template;

        $group = 'whatsapp_settings';
        $model = new \App\Models\Organization_Settings();
        $src_no = $model->getVal($group, 'source_number',$org_id);
        $api_key = $model->getVal($group, 'api_key',$org_id);
        $curl = curl_init();

        $post_data = [];
        // dd($message);
        $post_data['channel'] = $api['channel'];
        $post_data['source'] = $src_no;
        $post_data['destination'] = $destination;
        $post_data['src.name'] = $api['src_name'];
        $post_data['template'] = $template;
        $post_data['message'] = $message;

        // dd($post_data);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $api['whatsapp_api_url'],
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
    }
}
