using DataCenter.Common;
using DataCenter.Data.Attributes;
using DataCenter.Data.Base;
using DataCenter.Data.Enums;
using System;
using System.ComponentModel.DataAnnotations;

namespace DataCenter.Data.Models
{
    public class Phone : BaseEntity, ICreatedDate, IUpdatedDate
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Subject { get; set; }

        [Required]
        public long Number { get; set; }

        [Required]
        public PhoneSubjectType SubjectType { get; set; }

        [Required]
        public double Balance { get; set; }

        public DateTime CreatedDate { get; set; }

        public DateTime UpdatedDate { get; set; }
    }
}