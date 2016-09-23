<?php

namespace seregazhuk\tests\Api;

use seregazhuk\PinterestBot\Helpers\UrlBuilder;
use seregazhuk\tests\Helpers\FollowResponseHelper;
use seregazhuk\PinterestBot\Api\Providers\Pinners;

/**
 * Class PinnersTest.
 */
class PinnersTest extends ProviderTest
{
    use FollowResponseHelper;
    /**
     * @var Pinners
     */
    protected $provider;

    /**
     * @var string
     */
    protected $providerClass = Pinners::class;

    /** @test */
    public function it_should_follow_user()
    {
        $pinnerId = 1;
        $this->setFollowSuccessResponse($pinnerId, UrlBuilder::RESOURCE_FOLLOW_USER);
        $this->assertTrue($this->provider->follow($pinnerId));

        $this->setFollowErrorResponse($pinnerId, UrlBuilder::RESOURCE_FOLLOW_USER);
        $this->assertFalse($this->provider->follow($pinnerId));
    }

    /** @test */
    public function it_should_unfollow_user()
    {
        $pinnerId = 1;
        $this->setFollowSuccessResponse($pinnerId, UrlBuilder::RESOURCE_UNFOLLOW_USER);
        $this->assertTrue($this->provider->unFollow($pinnerId));

        $this->setFollowErrorResponse($pinnerId, UrlBuilder::RESOURCE_UNFOLLOW_USER);
        $this->assertFalse($this->provider->unFollow($pinnerId));
    }

    /** @test */
    public function it_should_return_user_info()
    {
        $userInfo = ['name' => 'test'];
        $response = $this->createApiResponseWithData($userInfo);
        $this->apiShouldReturn($response);

        $data = $this->provider->info('username');
        $this->assertEquals($userInfo, $data);
    }

    /** @test */
    public function it_should_return_generator_with_user_followers()
    {
        $response = $this->createPaginatedResponse();
        $this->apiShouldReturn($response);
        $this->apiShouldReturnEmpty(2);

        $followers = $this->provider->followers('username');
        $this->assertInstanceOf(\Generator::class, $followers);
        $this->assertCount(2, iterator_to_array($followers));

        $followers = $this->provider->followers('username');
        $this->assertEmpty(iterator_to_array($followers));
    }

    /** @test */
    public function it_should_return_generator_with_following_users()
    {
        $response = $this->createPaginatedResponse();
        $this->apiShouldReturn($response);
        $this->apiShouldReturnEmpty();

        $following = $this->provider->following('username');
        $this->assertCount(2, iterator_to_array($following));
    }

    /** @test */
    public function it_should_return_generator_with_user_pins()
    {
        $res = [
            'resource'          => [
                'options' => [
                    'bookmarks' => ['my_bookmarks'],
                ],
            ],
            'resource_response' => [
                'data' => [
                    ['id' => 1],
                    ['id' => 2],
                ],
            ],
        ];
        $this->apiShouldReturn($res);

        $pins = $this->provider->pins('username', 2);
        $expectedResultsNum = count($res['resource_response']['data']);
        $this->assertCount($expectedResultsNum, iterator_to_array($pins));
    }

    /** @test */
    public function it_should_return_generator_when_searching()
    {
        $response['module']['tree']['data']['results'] = [
            ['id' => 1],
            ['id' => 2],
        ];

        $expectedResultsNum = count($response['module']['tree']['data']['results']);
        $this->apiShouldReturn($response);

        $res = iterator_to_array($this->provider->search('dogs', 2));
        $this->assertCount($expectedResultsNum, $res);
    }

    /**
     * @test
     * @expectedException \seregazhuk\PinterestBot\Exceptions\WrongFollowingType
     */
    public function it_throws_exception_for_wrong_following_request()
    {
        $this->provider->following('test', 'unknown');
    }
}
