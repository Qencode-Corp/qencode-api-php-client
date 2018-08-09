<?php

namespace Qencode\Classes;

class Stream {
    /**
     * Output video frame size in pixels ("width"x"height"). Defaults to original frame size.
     * @var string
     */
    public $size;

    /**
     * Output stream video codec. Defaults to libx264. Possible values are: libx264, libx265, libvpx, libvpx-vp9.
     * @var string
     */
    public $video_codec;

    /**
     * Output video stream bitrate in kylobytes. Defaults to 512.
     * Note: don't specify bitrate unless you want constant bitrate for the video. To create variable bitrate use quality param.
     * @var string
     */
    public $bitrate;

    /**
     * Output video stream quality (aka Constant rate factor). Use this param to produce optimized videos with variable bitrate.
     * For H.264 the range is 0-51: where 0 is lossless and 51 is worst possible.
     * A lower value is a higher quality and a subjectively sane range is 18-28.
     * Consider 18 to be visually lossless or nearly so: it should look the same or nearly the same as the input but it isn't technically lossless.
     * @var int
     */
    public $quality;

    /**
     * Rotate video through specified degrees value. Possible values are 90, 180, 270.
     * @var int
     */
    public $rotate;

    /**
     * Output video frame rate. Defaults to original frame rate.
     * @var string
     */
    public $framerate;

    /**
     * Output video pixel format. Possible values are yuv420p, yuv422p, yuvj420p, yuvj422p. Defaults to yuv420p.
     * @var string
     */
    public $pix_format;

    /**
     * x264 video codec settings profile. Possible values are high, main, baseline. Defaults to main.
     * @var string
     */
    public $profile;

    /**
     * Output stream video codec parameters.
     * @var VideoCodecParameters
     */
    public $video_codec_parameters;

    /**
     * Keyframe period (in frames). Defaults to 90.
     * @var int
     */
    public $keyframe;

    /**
     * Segment duration to split media (in seconds). Defaults to 8.
     * @var int
     */
    public $segment_duration;

    /**
     * Specifies the start time (in seconds) in input video to begin transcoding from.
     * @var float
     */
    public $start_time;

    /**
     * Specifies duration of the video fragment (in seconds) to be transcoded.
     * @var float
     */
    public $duration;

    /**
     * Output file audio bitrate value in kylobytes. Defaults to 64.
     * @var int
     */
    public $audio_bitrate;

    /**
     * Output file audio sample rate. Defaults to 44100.
     * @var int
     */
    public $audio_sample_rate;

    /**
     * Output file audio channels number. Default value is 2.
     * @var int
     */
    public $audio_channels_number;

    /**
     * Output file audio codec name. Possible values are: aac, vorbis. Defaults to aac.
     * @var string
     */
    public $audio_codec;

    /**
     * Defaults to stereo
     * @var string
     */
    //public $downmix_mode;

}