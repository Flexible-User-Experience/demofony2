<?php
namespace Demofony2\AppBundle\Enum;

class ProposalStateEnum extends Enum
{
    const DRAFT       = 0;
    const MODERATION_PENDING       = 1;
    const DEBATE       = 2;
    const CLOSED       = 3;

    public static function getTranslations()
    {
        return array(
            static::DEBATE => 'proposal.state.debate',
            static::MODERATION_PENDING => 'proposal.state.moderation_pending',
            static::DEBATE => 'proposal.state.debate',
            static::CLOSED => 'proposal.state.closed',
        );
    }
}
