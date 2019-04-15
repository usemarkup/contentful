<?php
declare(strict_types=1);

namespace Markup\Contentful\Tests\Analysis;

use Markup\Contentful\Analysis\ResponseAnalyzer;
use Markup\Contentful\Analysis\ResponseAnalyzerInterface;
use PHPUnit\Framework\TestCase;

class ResponseAnalyzerTest extends TestCase
{
    /**
     * @var ResponseAnalyzer
     */
    private $analyzer;

    protected function setUp()
    {
        $this->analyzer = new ResponseAnalyzer();
    }

    public function testIsAnalyzer()
    {
        $this->assertInstanceOf(ResponseAnalyzerInterface::class, $this->analyzer);
    }

    public function testAnalysisOfEmptyStringGivesEmptyAnalysis()
    {
        $analysis = $this->analyzer->analyze('');
        $this->assertNull($analysis->getIndicatedResponseCount());
        $this->assertFalse($analysis->indicatesError());
    }

    public function testAnalysisOfGoodArrayGivesCorrectAnalysis()
    {
        $analysis = $this->analyzer->analyze($this->getGoodArrayResponse());
        $this->assertEquals(3, $analysis->getIndicatedResponseCount());
        $this->assertFalse($analysis->indicatesError());
    }

    public function testInvalidResponseGivesCorrectAnalysis()
    {
        $analysis = $this->analyzer->analyze($this->getInvalidResponse());
        $this->assertNull($analysis->getIndicatedResponseCount());
        $this->assertTrue($analysis->indicatesError());
    }

    public function testAnalysisOfGoodEntryGivesCorrectAnalysis()
    {
        $analysis = $this->analyzer->analyze($this->getGoodEntryResponse());
        $this->assertEquals(1, $analysis->getIndicatedResponseCount());
        $this->assertFalse($analysis->indicatesError());
    }

    private function getGoodArrayResponse(): string
    {
        return '{
  "sys": {
    "type": "Array"
  },
  "total": 3,
  "skip": 0,
  "limit": 1,
  "items": [
    {
      "sys": {
        "space": {
          "sys": {
            "type": "Link",
            "linkType": "Space",
            "id": "kjhkjhkljhklh"
          }
        },
        "id": "jlkjlkjlkjlkjlkj",
        "type": "Entry",
        "createdAt": "2017-08-07T13:51:38.741Z",
        "updatedAt": "2019-03-25T22:02:08.231Z",
        "environment": {
          "sys": {
            "id": "master",
            "type": "Link",
            "linkType": "Environment"
          }
        },
        "revision": 26,
        "contentType": {
          "sys": {
            "type": "Link",
            "linkType": "ContentType",
            "id": "jkhkljhlkjhkljhklj"
          }
        },
        "locale": "fr-FR"
      },
      "fields": {
        "heading": "Heading",
        "banners": [
          {
            "sys": {
              "type": "Link",
              "linkType": "Entry",
              "id": "kljhkljhkljhkljhkl"
            }
          }
        ],
        "reference": "ref-42",
        "markets": [
          "gb",
          "us",
          "de",
          "it",
          "pl",
          "ru"
        ],
        "publishedDate": "2019-02-20T23:55+00:00"
      }
    }
  ]
}
';
    }

    private function getInvalidResponse(): string
    {
        return '{
  "sys": {
    "type": "Error",
    "id": "InvalidQuery"
  },
  "message": "The query you sent was invalid. Probably a filter or ordering specification is not applicable to the type of a field.",
  "details": {
    "errors": [
      {
        "name": "unknownContentType",
        "value": "DOESNOTEXIST"
      }
    ]
  },
  "requestId": "c98d82bfb4f77bda88cd03307dbaeb2d"
}';
    }

    private function getGoodEntryResponse(): string
    {
        return '{
  "sys": {
    "space": {
      "sys": {
        "type": "Link",
        "linkType": "Space",
        "id": "x3a5wchdg6mu"
      }
    },
    "id": "4BAKXzkITKYE6yCE4IiEUS",
    "type": "Entry",
    "createdAt": "2018-11-14T08:02:21.892Z",
    "updatedAt": "2019-04-15T08:48:41.948Z",
    "environment": {
      "sys": {
        "id": "master",
        "type": "Link",
        "linkType": "Environment"
      }
    },
    "revision": 15,
    "contentType": {
      "sys": {
        "type": "Link",
        "linkType": "ContentType",
        "id": "6gge3FmsN2kwyQ24Qu6iO8"
      }
    },
    "locale": "fr-FR"
  },
  "fields": {
    "name": "READ THE LATEST - Editorials - Lookout - CTA",
    "text": "Lire L\'Article",
    "linkType": "global entry",
    "linkKey": "5o8iGsqooPcLflGtJ1B8Tx"
  }
}
';
    }
}
