<?php

namespace Qencode\Classes;

abstract class VideoCodecParameters {}

class Libx264_VideoCodecParameters extends VideoCodecParameters {

    /**
     * x264 video codec settings profile. Possible values are high, main, baseline. Defaults to main.
     * @var string
     */
    public $vprofile;

    /**
     * Set of constraints that indicate a degree of required decoder performance for a profile.
     * @var string
     */
    public $level;

    /**
     * Context-Adaptive Binary Arithmetic Coding (CABAC) is the default entropy encoder used by x264. Possible values are 1 and 0. Defaults to 1.
     * @var string
     */
    public $coder;

    /**
     * Allows B-frames to be kept as references. Possible values are +bpyramid, +wpred, +mixed_refs, +dct8×8, -fastpskip/+fastpskip, +aud Defaults to None.
     * @var string
     */
    public $flags2;

    /**
     * One of x264's most useful features is the ability to choose among many combinations of inter and intra partitions.
     * Possible values are +partp8x8, +partp4x4, +partb8x8, +parti8x8, +parti4x4. Defaults to None.
     * @var string
     */
    public $partitions;

    /**
     *
     * @var string
     */
    public $bf;

    /**
     * Defines motion detection type: 0 -- none, 1 -- spatial, 2 -- temporal, 3 -- auto. Defaults to 1.
     * @var string
     */
    public $directpred;

    /**
     * Motion Estimation method used in encoding. Possible values are epzs, hex, umh, full. Defaults to None.
     * @var string
     */
    public $me_method;
}