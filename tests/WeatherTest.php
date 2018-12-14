<?php
/**
 * Auth:chenyu.
 * Mail:phpdi@sina.com
 * Date: 18-12-11
 * Desc:
 */

namespace Phpdi\Weather\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Mockery\Matcher\AnyArgs;
use Phpdi\Weather\Exceptions\HttpException;
use Phpdi\Weather\Exceptions\InvalidArgumentException;
use Phpdi\Weather\Weather;
use PHPUnit\Framework\TestCase;

class WeatherTest extends TestCase
{
    public function testGetWeather()
    {
        //json
        $response = new Response(200, [], '{"success":true}');
        $client=\Mockery::mock(Client::class);

        $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
            'query' => [
                'key' => 'mock-key',
                'city' => '成都',
                'output' => 'json',
                'extensions' => 'base'
            ]
        ])->andReturn($response);

        $w=\Mockery::mock(Weather::class,['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->assertSame(['success' => true], $w->getWeather('成都'));

        //xml

        $response = new Response(200, [], '<hello>content</hello>');
        $client=\Mockery::mock(Client::class);

        $client->allows()->get('https://restapi.amap.com/v3/weather/weatherInfo', [
            'query' => [
                'key' => 'mock-key',
                'city' => '成都',
                'output' => 'xml',
                'extensions' => 'all'
            ]
        ])->andReturn($response);

        $w=\Mockery::mock(Weather::class,['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->assertSame('<hello>content</hello>', $w->getWeather('成都','all','xml'));



    }

    public function testGetHttpClient()
    {
        $w = new Weather('mock-key');
        $this->assertInstanceOf(ClientInterface::class, $w->getHttpClient());
    }

    public function testSetGuzzleOptions()
    {
        $w = new Weather('mock-key');

        $this->assertNull($w->getHttpClient()->getConfig('timeout'));

        $w->setGuzzleOptions(['timeout' => 5000]);

        $this->assertSame(5000, $w->getHttpClient()->getConfig('timeout'));
    }

    public function testGetWeatherWithGuzzleRuntimeException()
    {
        $client = \Mockery::mock(Client::class);

        $client->allows()
            ->get(new AnyArgs())
            ->andThrow(new \Exception('request timeout'));

        $w=\Mockery::mock(Weather::class,['mock-key'])->makePartial();

        $w->allows()->getHttpClient()->andReturn($client);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('request timeout');

        $w->getWeather('成都');
    }

    /**
     * 检查type参数
     * @throws InvalidArgumentException
     * @throws \Phpdi\Weather\Exceptions\HttpException
     */
    public function testGetWeatherWithInvalidType()
    {
        $w = new Weather('mock-key');

        //断言会抛出InvalidArgumentException::class异常
        $this->expectException(InvalidArgumentException::class);

        //断言异常消息为'Invalid response format:array'
        $this->expectExceptionMessage('Invalid response format:array');

        $w->getWeather('成都', 'base', 'array');

        //如果没有抛出异常,就会运行到这里,标记当前测试失败
        $this->fail('检查type参数,测试失败');

    }

    /**
     * 检查format参数
     * @throws InvalidArgumentException
     * @throws \Phpdi\Weather\Exceptions\HttpException
     */
    public function testGetWeatherWithInvalidFormat()
    {
        $w = new Weather('mock-key');

        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage('Invalid response format:array');

        $w->getWeather('成都', 'base', 'array');

        $this->fail('检查format参数,测试失败');
    }


    public function testGetLiveWeather()
    {
        //将getWeather接口模拟为返回固定内容,以测试参数传递是否正确
        /**@var \Phpdi\Weather\Weather @w*/
        $w=\Mockery::mock(Weather::class,['mock-key'])->makePartial();

        $w->expects()->getWeather('成都', 'base', 'json')->andReturn(['success' => true]);

        $this->assertSame(['success' => true], $w->getLiveWeather('成都'));
    }

    public function testGetForecastsWeather()
    {
        $w=\Mockery::mock(Weather::class,['mock-key'])->makePartial();

        $w->expects()->getWeather('成都', 'all', 'json')->andReturn(['success' => true]);

        $this->assertSame(['success' => true], $w->getForecastsWeather('成都'));
    }

}